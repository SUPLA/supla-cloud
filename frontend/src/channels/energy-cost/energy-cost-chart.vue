<script setup>
  import {nextTick, onBeforeUnmount, ref, watch} from 'vue';
  import ApexCharts from 'apexcharts';
  import {DateTime} from 'luxon';
  import {useI18n} from 'vue-i18n';
  import {formatDecimal} from './energy-cost-result-utils';

  const props = defineProps({
    buckets: {type: Array, default: () => []},
    energyBuckets: {type: Array, default: () => []},
    currency: String,
    timezone: {type: String, required: true},
    granularity: {type: String, required: true},
  });
  const emit = defineEmits(['rendering']);
  const i18n = useI18n();
  const element = ref();
  let chart;
  let renderToken = 0;
  const bucketDate = (value, tooltip = false) => {
    const format = props.granularity === 'month' ? 'LLL yyyy' : props.granularity === 'day' ? (tooltip ? 'dd LLL yyyy' : 'dd LLL') : 'dd LLL HH:mm';
    return DateTime.fromISO(value, {setZone: true}).setZone(props.timezone).toFormat(format);
  };
  async function render() {
    const token = ++renderToken;
    emit('rendering', true);
    if (!props.buckets.length && !props.energyBuckets.length) {
      chart?.destroy();
      chart = undefined;
      emit('rendering', false);
      return;
    }
    await nextTick();
    await new Promise(requestAnimationFrame);
    if (token !== renderToken) return;
    const components = [...new Set(props.buckets.flatMap((bucket) => Object.keys(bucket.byComponent)))];
    const energySeries = [
      {name: i18n.t('Imported energy'), type: 'line', data: props.energyBuckets.map((bucket) => Number(formatDecimal(bucket.imported)))},
      {name: i18n.t('Exported energy'), type: 'line', data: props.energyBuckets.map((bucket) => Number(formatDecimal(bucket.exported)))},
    ];
    chart?.destroy();
    chart = new ApexCharts(element.value, {
      chart: {type: 'line', stacked: true, height: 340, animations: {enabled: false}, toolbar: {show: true}},
      series: [
        ...components.map((name) => ({name, type: 'bar', data: props.buckets.map((bucket) => Number(formatDecimal(bucket.byComponent[name] || 0)))})),
        ...energySeries,
      ],
      xaxis: {
        categories: props.buckets.map((bucket) => new Date(bucket.from).getTime()),
        type: 'datetime',
        labels: {formatter: (_, timestamp) => bucketDate(new Date(timestamp).toISOString())},
      },
      yaxis: [
        {seriesName: components, labels: {formatter: (value) => `${formatDecimal(value)} ${props.currency || ''}`}},
        {seriesName: energySeries.map((series) => series.name), opposite: true, labels: {formatter: (value) => `${formatDecimal(value)} kWh`}},
      ],
      tooltip: {
        x: {formatter: (_, {dataPointIndex}) => bucketDate(props.buckets[dataPointIndex]?.from, true)},
        y: {formatter: (value, {seriesIndex}) => `${formatDecimal(value)} ${seriesIndex < components.length ? props.currency || '' : 'kWh'}`},
      },
      legend: {position: 'top'},
      noData: {text: 'No cost data in this range'},
    });
    await chart.render();
    if (token === renderToken) emit('rendering', false);
  }
  watch(() => [props.buckets, props.energyBuckets, props.currency, props.timezone, props.granularity], render, {deep: true});
  onBeforeUnmount(() => {
    chart?.destroy();
    emit('rendering', false);
  });
</script>

<template>
  <div ref="element"></div>
</template>

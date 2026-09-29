<script setup>
  import {nextTick, onBeforeUnmount, ref, watch} from 'vue';
  import ApexCharts from 'apexcharts';
  import {useI18n} from 'vue-i18n';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {formatDecimal} from './energy-cost-result-utils';

  const props = defineProps({
    buckets: {type: Array, default: () => []},
    energyBuckets: {type: Array, default: () => []},
    loading: Boolean,
    currency: String,
  });
  const i18n = useI18n();
  const element = ref();
  const rendering = ref(false);
  let chart;
  let renderToken = 0;
  async function render() {
    const token = ++renderToken;
    rendering.value = true;
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
      xaxis: {categories: props.buckets.map((bucket) => new Date(bucket.from).getTime()), type: 'datetime'},
      yaxis: [
        {seriesName: components, labels: {formatter: (value) => `${formatDecimal(value)} ${props.currency || ''}`}},
        {seriesName: energySeries.map((series) => series.name), opposite: true, labels: {formatter: (value) => `${formatDecimal(value)} kWh`}},
      ],
      tooltip: {
        x: {format: 'dd MMM yyyy HH:mm'},
        y: {formatter: (value, {seriesIndex}) => `${formatDecimal(value)} ${seriesIndex < components.length ? props.currency || '' : 'kWh'}`},
      },
      legend: {position: 'top'},
      noData: {text: 'No cost data in this range'},
    });
    await chart.render();
    if (token === renderToken) rendering.value = false;
  }
  watch(() => [props.buckets, props.energyBuckets, props.currency], render, {deep: true});
  onBeforeUnmount(() => chart?.destroy());
</script>

<template>
  <loading-cover :loading="loading || rendering" :debounce="0">
    <div ref="element"></div>
  </loading-cover>
</template>

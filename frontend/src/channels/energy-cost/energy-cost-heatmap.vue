<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {nextTick, onBeforeUnmount, onMounted, ref, watch} from 'vue';
  import ApexCharts from 'apexcharts';
  import {useI18n} from 'vue-i18n';
  import {formatDecimal} from './energy-cost-result-utils';

  const props = defineProps({
    buckets: {type: Array, default: () => []},
    energyBuckets: {type: Array, default: () => []},
    currency: String,
    metric: {type: String, required: true},
  });
  const emit = defineEmits(['rendering']);
  const i18n = useI18n();
  const element = ref();
  const mobile = ref(false);
  const weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
  let chart;
  let renderToken = 0;
  let mediaQuery;

  const weekdayName = (weekday) => i18n.t(weekdays[weekday - 1]);
  function updateMobile() {
    mobile.value = mediaQuery.matches;
  }
  function cellValue(bucket, hour) {
    const cost = Number(bucket.hours[hour]);
    if (props.metric === 'cost') return cost;
    const imported = Number(props.energyBuckets[bucket.weekday - 1]?.hours[hour] || 0);
    if (props.metric === 'usage') return imported;
    return imported ? cost / imported : null;
  }
  function visibleBuckets() {
    return props.buckets.filter((bucket) => bucket.available || props.energyBuckets[bucket.weekday - 1]?.available);
  }

  async function render() {
    const token = ++renderToken;
    emit('rendering', true);
    if (!visibleBuckets().length) {
      chart?.destroy();
      chart = undefined;
      emit('rendering', false);
      return;
    }
    await nextTick();
    await new Promise(requestAnimationFrame);
    if (token !== renderToken) return;
    chart?.destroy();
    const buckets = visibleBuckets();
    chart = new ApexCharts(element.value, {
      chart: {type: 'heatmap', height: 340, animations: {enabled: false}, toolbar: {show: true}},
      colors: ['#f60'],
      series: mobile.value
        ? Array.from({length: 24}, (_, hour) => ({
            name: String(hour).padStart(2, '0'),
            data: buckets.map((bucket) => ({x: weekdayName(bucket.weekday), y: cellValue(bucket, hour)})),
          })).reverse()
        : buckets
            .map((bucket) => ({
              name: weekdayName(bucket.weekday),
              data: bucket.hours.map((_, hour) => ({x: String(hour).padStart(2, '0'), y: cellValue(bucket, hour)})),
            }))
            .reverse(),
      dataLabels: {enabled: false},
      xaxis: {title: {text: i18n.t(mobile.value ? 'Weekday' : 'Hour')}},
      yaxis: {title: {text: i18n.t(mobile.value ? 'Hour' : 'Weekday')}},
      tooltip: {
        y: {
          formatter: (value) =>
            `${formatDecimal(value)} ${props.metric === 'usage' ? 'kWh' : props.metric === 'cost' ? props.currency || '' : `${props.currency || ''}/kWh`}`,
        },
      },
      noData: {text: i18n.t('No cost data in this range')},
    });
    await chart.render();
    if (token === renderToken) emit('rendering', false);
  }

  watch(() => [props.buckets, props.energyBuckets, props.currency, props.metric, mobile.value], render, {deep: true});
  onMounted(() => {
    mediaQuery = window.matchMedia('(max-width: 767px)');
    updateMobile();
    mediaQuery.addEventListener('change', updateMobile);
  });
  onBeforeUnmount(() => {
    chart?.destroy();
    mediaQuery?.removeEventListener('change', updateMobile);
    emit('rendering', false);
  });
</script>

<template>
  <div ref="element"></div>
</template>

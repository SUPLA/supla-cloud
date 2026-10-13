<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import {ref, watch, onMounted, onBeforeUnmount} from 'vue';
  import ApexCharts from 'apexcharts';
  import {DateTime} from 'luxon';
  import {useI18n} from 'vue-i18n';
  import {formatDecimal} from './energy-cost-result-utils';
  const props = defineProps({buckets: Array, currency: String, timezone: String, alternativeLabel: String});
  const {t} = useI18n();
  const element = ref();
  let chart;
  function render() {
    chart?.destroy();
    chart = new ApexCharts(element.value, {
      chart: {type: 'bar', height: 340, animations: {enabled: false}},
      colors: ['#00d151', '#5bc0de'],
      fill: {opacity: 1},
      stroke: {width: 0},
      dataLabels: {enabled: false},
      series: [
        ['current', 'Current plan'],
        ['alternative', 'Alternative tariff'],
      ].map(([key, label]) => ({
        name: key === 'alternative' && props.alternativeLabel ? props.alternativeLabel : t(label),
        data: props.buckets.map((bucket) => ({x: Date.parse(bucket.from), y: bucket[key] === null ? null : Number(bucket[key])})),
      })),
      xaxis: {type: 'datetime', labels: {formatter: (_, timestamp) => DateTime.fromMillis(timestamp, {zone: props.timezone}).toFormat('dd LLL HH:mm')}},
      yaxis: {labels: {formatter: (value) => `${formatDecimal(value)} ${props.currency}`}},
      tooltip: {y: {formatter: (value) => (value == null ? '—' : `${formatDecimal(value)} ${props.currency}`)}},
      legend: {position: 'top'},
    });
    chart.render();
  }
  onMounted(render);
  watch(() => [props.buckets, props.currency, props.timezone, props.alternativeLabel], render);
  onBeforeUnmount(() => chart?.destroy());
</script>
<template><div ref="element"></div></template>

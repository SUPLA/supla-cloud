<script setup>
  import {onBeforeUnmount, ref, watch} from 'vue';
  import ApexCharts from 'apexcharts';
  import {formatDecimal} from './energy-cost-result-utils';

  const props = defineProps({buckets: {type: Array, default: () => []}, loading: Boolean, currency: String});
  const element = ref();
  let chart;
  function render() {
    const components = [...new Set(props.buckets.flatMap((bucket) => Object.keys(bucket.byComponent)))];
    chart?.destroy();
    chart = new ApexCharts(element.value, {
      chart: {type: 'bar', stacked: true, height: 340, animations: {enabled: false}, toolbar: {show: true}},
      series: components.map((name) => ({name, data: props.buckets.map((bucket) => Number(formatDecimal(bucket.byComponent[name] || 0)))})),
      xaxis: {categories: props.buckets.map((bucket) => new Date(bucket.from).getTime()), type: 'datetime'},
      yaxis: {labels: {formatter: (value) => `${formatDecimal(value)} ${props.currency || ''}`}},
      tooltip: {x: {format: 'dd MMM yyyy HH:mm'}, y: {formatter: (value) => `${formatDecimal(value)} ${props.currency || ''}`}},
      legend: {position: 'top'},
      noData: {text: 'No cost data in this range'},
    });
    chart.render();
  }
  watch(() => [props.buckets, props.currency], render, {deep: true});
  onBeforeUnmount(() => chart?.destroy());
</script>

<template>
  <div class="position-relative">
    <div ref="element"></div>
    <div v-if="loading" class="energy-cost-chart-loading">{{ $t('Loading...') }}</div>
  </div>
</template>

<style scoped>
  .energy-cost-chart-loading {
    position: absolute;
    inset: 0;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.55);
  }
</style>

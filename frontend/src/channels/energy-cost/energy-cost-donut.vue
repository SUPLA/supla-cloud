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
  import {addDecimals, formatDecimal} from './energy-cost-result-utils';

  const props = defineProps({items: {type: Array, default: () => []}, currency: String});
  const i18n = useI18n();
  const element = ref();
  let chart;

  async function render() {
    await nextTick();
    chart?.destroy();
    chart = new ApexCharts(element.value, {
      chart: {type: 'donut', height: 240, toolbar: {show: false}},
      labels: props.items.map((item) => item.label),
      series: props.items.map((item) => Number(item.amount)),
      dataLabels: {enabled: false},
      legend: {show: false},
      plotOptions: {
        pie: {
          donut: {
            labels: {
              show: true,
              value: {formatter: (value) => `${formatDecimal(value)} ${props.currency || ''}`},
              total: {
                show: true,
                label: i18n.t('Total'),
                formatter: () => `${formatDecimal(props.items.reduce((total, item) => addDecimals(total, item.amount), '0'))} ${props.currency || ''}`,
              },
            },
          },
        },
      },
      tooltip: {y: {formatter: (value) => `${formatDecimal(value)} ${props.currency || ''}`}},
    });
    await chart.render();
  }

  watch(() => [props.items, props.currency], render, {deep: true});
  onMounted(render);
  onBeforeUnmount(() => chart?.destroy());
</script>

<template>
  <div ref="element"></div>
</template>

<script setup>
  import {nextTick, onBeforeUnmount, ref, watch} from 'vue';
  import ApexCharts from 'apexcharts';
  import {DateTime} from 'luxon';
  import {useI18n} from 'vue-i18n';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {energyPriceApi} from '@/api/energy-price-api';

  const props = defineProps({channel: {type: Object, required: true}});
  const i18n = useI18n();
  const element = ref();
  const logs = ref([]);
  const loading = ref(false);
  const error = ref(null);
  let chart;
  let requestToken = 0;

  const formatPrice = (value) => `${Number(value).toLocaleString(i18n.locale.value, {minimumFractionDigits: 2, maximumFractionDigits: 2})} zł/MWh`;
  const formatDate = (timestamp) => DateTime.fromMillis(timestamp).setLocale(i18n.locale.value).toFormat('dd LLL yyyy HH:mm');

  async function render() {
    await nextTick();
    chart?.destroy();
    chart = new ApexCharts(element.value, {
      chart: {type: 'line', height: 340, animations: {enabled: false}, toolbar: {show: true}},
      series: [
        {
          name: props.channel.caption,
          data: logs.value.map(({dateTimestamp, value}) => ({x: dateTimestamp * 1000, y: value})),
        },
      ],
      stroke: {width: 2},
      xaxis: {type: 'datetime'},
      yaxis: {labels: {formatter: formatPrice}},
      tooltip: {
        x: {formatter: formatDate},
        y: {formatter: formatPrice},
      },
      noData: {text: i18n.t('No price data in this range')},
    });
    await chart.render();
  }

  async function load() {
    const token = ++requestToken;
    loading.value = true;
    error.value = null;
    try {
      const loadedLogs = await energyPriceApi.getLogs(props.channel.id);
      if (token !== requestToken) return;
      logs.value = loadedLogs;
      await render();
    } catch (requestError) {
      if (token === requestToken) error.value = requestError.body?.message || 'Could not load energy prices.'; // i18n
    } finally {
      if (token === requestToken) loading.value = false;
    }
  }

  watch(() => props.channel.id, load, {immediate: true});
  onBeforeUnmount(() => chart?.destroy());
</script>

<template>
  <div class="container channel-energy-prices">
    <loading-cover :loading="loading">
      <div v-if="error" class="alert alert-danger">{{ $t(error) }}</div>
      <div ref="element"></div>
    </loading-cover>
  </div>
</template>

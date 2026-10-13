<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import {onBeforeUnmount, ref, toRaw, watch} from 'vue';
  import EnergyCostHeatmapPanel from './energy-cost-heatmap-panel.vue';
  import EnergyCostBreakdown from './energy-cost-breakdown.vue';
  import EnergyCostDetails from './energy-cost-details.vue';

  const props = defineProps({result: {type: Object, required: true}, timezone: {type: String, required: true}});
  const heatmapMetric = ref('cost');
  const heatmapBuckets = ref([]);
  const heatmapEnergyBuckets = ref([]);
  const loading = ref(false);
  let request = 0;
  const worker = new Worker(new URL('./energy-cost-chart.worker.js', import.meta.url), {type: 'module'});
  worker.onmessage = ({data}) => {
    if (data.request !== request) return;
    heatmapBuckets.value = data.heatmapBuckets;
    heatmapEnergyBuckets.value = data.heatmapEnergyBuckets;
    loading.value = false;
  };
  watch(
    () => [props.result, props.timezone],
    () => {
      loading.value = true;
      const result = toRaw(props.result);
      worker.postMessage({charges: result.charges, intervals: result.intervals, timezone: props.timezone, request: ++request, heatmapsOnly: true});
    },
    {immediate: true}
  );
  onBeforeUnmount(() => {
    request++;
    worker.terminate();
  });
</script>

<template>
  <div class="energy-cost-comparison-details">
    <p v-if="loading" role="status">{{ $t('Calculating costs...') }}</p>
    <energy-cost-heatmap-panel v-else v-model="heatmapMetric" :buckets="heatmapBuckets" :energy-buckets="heatmapEnergyBuckets" :currency="result.currency" />
    <energy-cost-breakdown :result="result" :currency="result.currency" />
    <energy-cost-details :result="result" :timezone="timezone" />
  </div>
</template>

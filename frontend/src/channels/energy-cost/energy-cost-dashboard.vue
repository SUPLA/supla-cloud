<script setup>
  import {computed, onBeforeUnmount, onMounted, ref, toRaw, watch} from 'vue';
  import {DateTime} from 'luxon';
  import {energyCostApi} from '@/api/energy-cost-api';
  import EnergyCostRangeSelector from './energy-cost-range-selector.vue';
  import EnergyCostSummary from './energy-cost-summary.vue';
  import EnergyCostChart from './energy-cost-chart.vue';
  import EnergyCostBreakdown from './energy-cost-breakdown.vue';
  import {preferredGranularity} from './energy-cost-result-utils';
  import {energyCostCalculationStorage, scenarioFingerprint} from './energy-cost-calculation-storage';

  const props = defineProps({channel: {type: Object, required: true}, plan: {type: Object, required: true}});
  const timezone = computed(() => props.plan.configuration?.timezone || 'Europe/Warsaw');
  const storageKey = computed(() => `energy-cost-range:${props.channel.id}`);
  const defaultRange = () => {
    const now = DateTime.now().setZone(timezone.value);
    return {from: now.startOf('month').toISO(), to: now.startOf('hour').toISO()};
  };
  const range = ref(defaultRange());
  const result = ref(null);
  const loading = ref(false);
  const error = ref(null);
  const granularity = ref('day');
  const availableRange = ref(null);
  let requestToken = 0;
  let chartRequestToken = 0;

  const buckets = ref([]);
  const energyBuckets = ref([]);
  const chartWorking = ref(false);
  const chartGranularity = ref(null);
  const chartWorker = new Worker(new URL('./energy-cost-chart.worker.js', import.meta.url), {type: 'module'});
  chartWorker.onmessage = ({data}) => {
    if (data.request !== chartRequestToken) return;
    buckets.value = data.buckets;
    energyBuckets.value = data.energyBuckets;
    chartGranularity.value = data.granularity;
    chartWorking.value = false;
  };
  const rangeSeconds = computed(() => ({
    from: Math.floor(DateTime.fromISO(range.value.from).toSeconds()),
    to: Math.floor(DateTime.fromISO(range.value.to).toSeconds()),
  }));
  const fingerprint = computed(() => scenarioFingerprint(props.plan));

  function prepareChart() {
    chartRequestToken += 1;
    if (!result.value) {
      buckets.value = [];
      energyBuckets.value = [];
      chartWorking.value = false;
      chartGranularity.value = null;
      return;
    }
    chartWorking.value = true;
    const rawResult = toRaw(result.value);
    chartWorker.postMessage({
      charges: rawResult.charges,
      intervals: rawResult.intervals,
      granularity: granularity.value,
      timezone: timezone.value,
      request: chartRequestToken,
    });
  }

  function setRange(next) {
    const fitted = fitToAvailableRange(next);
    if (!fitted) {
      error.value = 'No complete meter data is available for the selected range.';
      return;
    }
    range.value = fitted;
    sessionStorage.setItem(storageKey.value, JSON.stringify(fitted));
    granularity.value = preferredGranularity(fitted.from, fitted.to, timezone.value);
  }
  function fitToAvailableRange(next) {
    if (!availableRange.value) return next;
    const from = DateTime.fromISO(next.from, {zone: timezone.value});
    const to = DateTime.fromISO(next.to, {zone: timezone.value});
    const availableFrom = DateTime.fromISO(availableRange.value.from, {zone: timezone.value});
    const availableTo = DateTime.fromISO(availableRange.value.to, {zone: timezone.value});
    const fittedFrom = from < availableFrom ? availableFrom : from;
    const fittedTo = to > availableTo ? availableTo : to;
    return fittedFrom < fittedTo ? {from: fittedFrom.toISO(), to: fittedTo.toISO()} : null;
  }
  async function fetchCalculation() {
    const token = ++requestToken;
    const {from, to} = rangeSeconds.value;
    const cached = await energyCostCalculationStorage.get(props.channel.id, fingerprint.value, from, to);
    if (token !== requestToken) return;
    if (cached) result.value = cached.result;
    const reachesNow = to >= DateTime.now().toSeconds() - 86400;
    const freshness = reachesNow ? 5 * 60 * 1000 : 6 * 60 * 60 * 1000;
    if (cached && Date.now() - cached.fetchedAt < freshness) return;
    loading.value = true;
    error.value = null;
    try {
      const next = await energyCostApi.calculate(props.channel.id, from, to);
      if (token !== requestToken) return;
      result.value = next;
      await energyCostCalculationStorage.put({
        key: energyCostCalculationStorage.key(props.channel.id, fingerprint.value, from, to),
        channelId: props.channel.id,
        scenarioType: 'saved-plan',
        scenarioId: props.plan.id,
        scenarioFingerprint: fingerprint.value,
        fromTimestamp: from,
        toTimestamp: to,
        fetchedAt: Date.now(),
        result: next,
      });
    } catch (requestError) {
      if (token === requestToken) error.value = requestError.body?.message || 'Could not calculate costs.';
    } finally {
      if (token === requestToken) loading.value = false;
    }
  }
  onMounted(async () => {
    await energyCostCalculationStorage.connect();
    try {
      const {oldest, newest} = await energyCostApi.getMeasurementBounds(props.channel.id);
      if (!oldest || !newest) {
        error.value = 'No meter data is available for cost calculation.';
        return;
      }
      const first = DateTime.fromSeconds(oldest.date_timestamp, {zone: timezone.value});
      const last = DateTime.fromSeconds(newest.date_timestamp, {zone: timezone.value});
      availableRange.value = {from: first.startOf('hour').plus({hours: 1}).toISO(), to: last.startOf('hour').toISO()};
      const fitted = fitToAvailableRange(range.value);
      if (!fitted) {
        error.value = 'No complete meter data is available for the selected range.';
        return;
      }
      range.value = fitted;
    } catch {
      // The calculation endpoint remains usable if measurement-history bounds cannot be loaded.
    }
    const saved = sessionStorage.getItem(storageKey.value);
    if (saved) {
      try {
        const savedRange = JSON.parse(saved);
        const fitted = fitToAvailableRange(savedRange);
        if (fitted && DateTime.fromISO(fitted.to) <= DateTime.now().setZone(timezone.value).startOf('hour')) setRange(fitted);
        else sessionStorage.removeItem(storageKey.value);
      } catch {
        sessionStorage.removeItem(storageKey.value);
      }
    }
    fetchCalculation();
  });
  watch([range, () => props.plan], fetchCalculation, {deep: true});
  watch([result, granularity, timezone], prepareChart, {deep: true});
  onBeforeUnmount(() => chartWorker.terminate());
</script>

<template>
  <section class="energy-cost-dashboard mt-4">
    <energy-cost-range-selector :model-value="range" :timezone="timezone" :billing-cycles="plan.configuration?.billingCycles" @update:model-value="setRange" />
    <div class="form-inline mb-2">
      <label class="mr-2">{{ $t('Aggregation') }}</label
      ><select v-model="granularity" class="form-control">
        <option value="hour">{{ $t('Hour') }}</option>
        <option value="day">{{ $t('Day') }}</option>
        <option value="month">{{ $t('Month') }}</option>
      </select>
    </div>
    <p v-if="chartGranularity && chartGranularity !== granularity" class="text-muted">
      {{ $t('Chart is displayed by {granularity} to keep it responsive.', {granularity: $t(chartGranularity)}) }}
    </p>
    <div v-if="error" class="alert alert-danger">{{ $t(error) }}</div>
    <template v-else-if="result">
      <energy-cost-summary :result="result" />
      <h3>{{ $t('Gross usage-based cost over time') }}</h3>
      <energy-cost-chart :buckets="buckets" :energy-buckets="energyBuckets" :loading="loading || chartWorking" :currency="result.currency" />
      <energy-cost-breakdown :result="result" :currency="result.currency" />
    </template>
    <div v-else-if="loading" class="well text-center">{{ $t('Calculating costs...') }}</div>
  </section>
</template>

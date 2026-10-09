<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed, nextTick, onBeforeUnmount, onMounted, ref, toRaw, watch} from 'vue';
  import {storeToRefs} from 'pinia';
  import {useRoute, useRouter} from 'vue-router';
  import {DateTime} from 'luxon';
  import logoUrl from '@/assets/img/logo.svg';
  import {energyCostApi} from '@/api/energy-cost-api';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import EnergyCostRangeSelector from './energy-cost-range-selector.vue';
  import EnergyCostSummary from './energy-cost-summary.vue';
  import EnergyCostChart from './energy-cost-chart.vue';
  import EnergyCostHeatmap from './energy-cost-heatmap.vue';
  import EnergyCostBreakdown from './energy-cost-breakdown.vue';
  import EnergyCostDetails from './energy-cost-details.vue';
  import {alignedBillingPeriodRange, pricePeriodsForComponent, presetDefault} from './energy-cost-plan-utils';
  import {preferredGranularity} from './energy-cost-result-utils';
  import {energyCostCalculationStorage, scenarioFingerprint} from './energy-cost-calculation-storage';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';

  const props = defineProps({channel: {type: Object, required: true}, plan: {type: Object, required: true}, assignedPlanId: Number});
  const route = useRoute();
  const router = useRouter();
  const energyCostStore = useEnergyCostStore();
  const {presetDetailsById} = storeToRefs(energyCostStore);
  const timezone = computed(() => props.plan.configuration?.timezone || 'Europe/Warsaw');
  const storageKey = computed(() => `energy-cost-range:${props.channel.id}`);
  const defaultRange = () => {
    const now = DateTime.now().setZone(timezone.value);
    return {from: now.startOf('month').toISO(), to: now.startOf('hour').toISO()};
  };
  const rangeFromUrl = () => {
    const {energyCostFrom: from, energyCostTo: to} = route.query;
    if (typeof from !== 'string' || typeof to !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(from) || !/^\d{4}-\d{2}-\d{2}$/.test(to)) {
      return null;
    }
    const fromDate = DateTime.fromISO(from, {zone: timezone.value}).startOf('day');
    const toDate = DateTime.fromISO(to, {zone: timezone.value}).plus({days: 1}).startOf('day');
    return fromDate.isValid && toDate.isValid && fromDate < toDate ? {from: fromDate.toISO(), to: toDate.toISO()} : null;
  };
  const range = ref(rangeFromUrl() || defaultRange());
  const result = ref(null);
  const loading = ref(false);
  const error = ref(null);
  const noLogsError = 'No logs are available for the selected range.';
  const errorAlertClass = computed(() => (error.value === noLogsError ? 'alert-info' : 'alert-danger'));
  const granularity = ref('day');
  const availableRange = ref(null);
  let requestToken = 0;
  let chartRequestToken = 0;
  let calculationWatchEnabled = false;

  const buckets = ref([]);
  const energyBuckets = ref([]);
  const heatmapBuckets = ref([]);
  const heatmapEnergyBuckets = ref([]);
  const heatmapMetric = ref('cost');
  const printHeatmapMetrics = [
    {metric: 'cost', title: 'Gross cost by weekday and hour', description: 'When did I spend the most?'},
    {metric: 'costPerKwh', title: 'Cost per kWh by weekday and hour', description: 'When is electricity intrinsically most expensive?'},
    {metric: 'usage', title: 'Energy usage by weekday and hour', description: 'When did I use the most energy?'},
  ];
  const chartWorking = ref(false);
  const chartRendering = ref(false);
  const heatmapRendering = ref(false);
  const chartGranularity = ref(null);
  const reportGeneratedAt = ref(DateTime.now().toISO());
  const presetDetailsLoading = ref(false);
  const tariffPeriods = computed(() => props.plan.configuration?.periods || []);
  const chartWorker = new Worker(new URL('./energy-cost-chart.worker.js', import.meta.url), {type: 'module'});
  chartWorker.onmessage = ({data}) => {
    if (data.request !== chartRequestToken) return;
    buckets.value = data.buckets;
    energyBuckets.value = data.energyBuckets;
    heatmapBuckets.value = data.heatmapBuckets;
    heatmapEnergyBuckets.value = data.heatmapEnergyBuckets;
    chartGranularity.value = data.granularity;
    chartWorking.value = false;
  };
  const rangeSeconds = computed(() => ({
    from: Math.floor(DateTime.fromISO(range.value.from).toSeconds()),
    to: Math.floor(DateTime.fromISO(range.value.to).toSeconds()),
  }));
  const catalogRevision = ref('');
  const fingerprint = computed(() => scenarioFingerprint(props.plan, catalogRevision.value));
  const simulating = computed(() => props.plan.id !== props.assignedPlanId);
  const dateTime = (value) => DateTime.fromISO(value, {setZone: true}).setZone(timezone.value).toFormat('dd LLL yyyy, HH:mm');
  const reportPeriod = computed(() => `${dateTime(range.value.from)} - ${dateTime(range.value.to)}`);
  const componentLabels = {
    ENERGY_PURCHASE: 'Energy purchase',
    DISTRIBUTION_VARIABLE: 'Variable distribution',
    DISTRIBUTION_FIXED: 'Fixed distribution',
    SUPPLIER_FIXED: 'Supplier fixed charge',
  };

  const pricingDetails = (component) => {
    if (component.presetId === undefined) return [{label: 'Rate', value: component.rate, unit: component.per}];
    const preset = presetDetailsById.value[component.presetId];
    return pricePeriodsForComponent(preset, component.componentId).flatMap(({period, inputs}) =>
      inputs.map((input) => {
        const overridden = Object.hasOwn(component.values || {}, input.id);
        const value = overridden ? component.values[input.id] : presetDefault(preset, input, component.componentId);
        const label = input.type === 'CHOICE' ? input.options?.find((option) => option.value === value)?.label || value : value;
        return {label: input.label, value: label, unit: input.unit, period, overridden};
      })
    );
  };

  async function loadPresetDetails() {
    const ids = [
      ...new Set(
        tariffPeriods.value
          .flatMap((period) => period.components || [])
          .map((component) => component.presetId)
          .filter(Boolean)
      ),
    ];
    presetDetailsLoading.value = true;
    try {
      await Promise.all(ids.map((id) => energyCostStore.fetchPreset(id)));
    } catch {
      // The report can still show inline rates when tariff preset details are unavailable.
    } finally {
      presetDetailsLoading.value = false;
    }
  }

  function updateReportGeneratedAt() {
    reportGeneratedAt.value = DateTime.now().setZone(timezone.value).toISO();
  }

  async function printReport() {
    updateReportGeneratedAt();
    await nextTick();
    window.print();
  }

  function prepareChart() {
    chartRequestToken += 1;
    if (!result.value) {
      buckets.value = [];
      energyBuckets.value = [];
      heatmapBuckets.value = [];
      heatmapEnergyBuckets.value = [];
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
      error.value = noLogsError;
      return;
    }
    error.value = null;
    range.value = fitted;
    sessionStorage.setItem(storageKey.value, JSON.stringify(fitted));
    const urlFrom = DateTime.fromISO(fitted.from, {setZone: true}).setZone(timezone.value).toISODate();
    const urlTo = DateTime.fromISO(fitted.to, {setZone: true}).setZone(timezone.value).minus({milliseconds: 1}).toISODate();
    if (route.query.energyCostFrom !== urlFrom || route.query.energyCostTo !== urlTo) {
      router.replace({query: {...route.query, energyCostFrom: urlFrom, energyCostTo: urlTo}});
    }
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
  function alignRangeWithBillingPeriods() {
    const aligned = alignedBillingPeriodRange(range.value, props.plan.configuration?.billingCycles || [], timezone.value, availableRange.value?.to);
    if (aligned) setRange(aligned);
  }
  async function fetchCalculation() {
    const token = ++requestToken;
    const {from, to} = rangeSeconds.value;
    error.value = null;
    if (!simulating.value) {
      // Refresh the compact catalog before accepting an IndexedDB result. Preset documents
      // are intentionally live, so a plan timestamp alone cannot identify a scenario.
      const catalog = await energyCostStore.fetchPresets(true);
      const referencedIds = new Set(
        (props.plan.configuration?.periods || []).flatMap((period) => (period.components || []).map((component) => component.presetId)).filter(Boolean)
      );
      catalogRevision.value = catalog
        .filter((preset) => referencedIds.has(preset.id))
        .map((preset) => `${preset.id}:${preset.revision}`)
        .sort()
        .join('|');
      if (token !== requestToken) return;
      const cached = await energyCostCalculationStorage.get(props.channel.id, fingerprint.value, from, to);
      if (token !== requestToken) return;
      if (cached) result.value = cached.result;
      const reachesNow = to >= DateTime.now().toSeconds() - 86400;
      const freshness = reachesNow ? 5 * 60 * 1000 : 6 * 60 * 60 * 1000;
      if (cached && Date.now() - cached.fetchedAt < freshness) return;
    }
    loading.value = true;
    try {
      const next = simulating.value
        ? await energyCostApi.simulate(props.channel.id, from, to, props.plan.configuration)
        : await energyCostApi.calculate(props.channel.id, from, to);
      if (token !== requestToken) return;
      result.value = next;
      if (!simulating.value) {
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
      }
    } catch (requestError) {
      if (token === requestToken) error.value = requestError.body?.message || 'Could not calculate costs.';
    } finally {
      if (token === requestToken) loading.value = false;
    }
  }
  onMounted(async () => {
    window.addEventListener('beforeprint', updateReportGeneratedAt);
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
        error.value = noLogsError;
        return;
      }
      setRange(fitted);
    } catch {
      // The calculation endpoint remains usable if measurement-history bounds cannot be loaded.
    }
    const saved = sessionStorage.getItem(storageKey.value);
    if (!rangeFromUrl() && saved) {
      try {
        const savedRange = JSON.parse(saved);
        const fitted = fitToAvailableRange(savedRange);
        if (fitted && DateTime.fromISO(fitted.to) <= DateTime.now().setZone(timezone.value).startOf('hour')) setRange(fitted);
        else sessionStorage.removeItem(storageKey.value);
      } catch {
        sessionStorage.removeItem(storageKey.value);
      }
    }
    await nextTick();
    calculationWatchEnabled = true;
    fetchCalculation();
  });
  watch(
    [range, () => props.plan],
    () => {
      if (calculationWatchEnabled) fetchCalculation();
    },
    {deep: true}
  );
  watch([result, granularity, timezone], prepareChart, {deep: true});
  watch(tariffPeriods, loadPresetDetails, {deep: true, immediate: true});
  onBeforeUnmount(() => {
    window.removeEventListener('beforeprint', updateReportGeneratedAt);
    chartWorker.terminate();
  });
</script>

<template>
  <section class="energy-cost-dashboard mt-4">
    <loading-cover :loading="loading || chartWorking || chartRendering || heatmapRendering" :debounce="0">
      <energy-cost-range-selector
        :model-value="range"
        :timezone="timezone"
        :billing-cycles="plan.configuration?.billingCycles"
        @update:model-value="setRange"
      />
      <p v-if="result?.processedDeltaCount && chartGranularity && chartGranularity !== granularity" class="text-muted">
        {{ $t('Chart is displayed by {granularity} to keep it responsive.', {granularity: $t(chartGranularity)}) }}
      </p>
      <div v-if="error" class="alert" :class="errorAlertClass">{{ $t(error) }}</div>
      <template v-else-if="result">
        <div v-if="result.processedDeltaCount === 0" class="alert alert-info">
          {{ $t('No logs are available for the selected range.') }}
        </div>
        <template v-if="result.processedDeltaCount > 0">
          <div v-if="result.incomplete" class="alert alert-warning">
            <p>{{ $t('Some logs could not be calculated. Results may be incomplete.') }}</p>
            <details v-if="result.warnings?.length">
              <summary>{{ $t('Warnings') }} ({{ result.warnings.length }})</summary>
              <ul class="mb-0">
                <li v-for="(warning, index) in result.warnings" :key="`${warning.code}-${warning.from}-${index}`">
                  <strong>{{ $t(warning.scope === 'TEMPORAL_NETTING_WINDOW' ? 'Metering window' : 'Meter interval') }}:</strong>
                  {{ dateTime(warning.from) }} - {{ dateTime(warning.to) }}
                  <span v-if="warning.componentId">, {{ $t('Component') }}: {{ warning.componentId }}</span>
                  <span v-if="warning.referenceDataId">, {{ $t('Reference data') }}: {{ warning.referenceDataId }}</span>
                  <div>{{ warning.message }}</div>
                </li>
              </ul>
            </details>
          </div>
          <header class="energy-cost-print-header">
            <img :src="logoUrl" alt="SUPLA" />
            <div class="energy-cost-print-title">
              <p>{{ $t('Energy cost report') }}</p>
              <span>{{ $t('by SUPLA') }}</span>
            </div>
            <div class="energy-cost-print-period">
              <span>{{ $t('Selected period') }}</span>
              <strong>{{ reportPeriod }}</strong>
            </div>
            <dl class="energy-cost-print-meta">
              <div>
                <dt>{{ $t('Channel') }}</dt>
                <dd>{{ channel.caption || `ID${channel.id}` }}</dd>
              </div>
              <div class="energy-cost-print-plan">
                <dt>{{ $t('Tariff plan') }}</dt>
                <dd>{{ plan.name }}</dd>
              </div>
              <div>
                <dt>{{ $t('Timezone') }}</dt>
                <dd>{{ timezone }}</dd>
              </div>
              <div>
                <dt>{{ $t('Currency') }}</dt>
                <dd>{{ result.currency }}</dd>
              </div>
            </dl>
            <div v-if="tariffPeriods.length" class="energy-cost-print-tariff">
              <h2>{{ $t('Tariff details') }}</h2>
              <div v-for="(period, index) in tariffPeriods" :key="index">
                <strong>{{ $t('Tariff period') }} {{ index + 1 }}</strong>
                <span>
                  {{ period.validFrom ? dateTime(period.validFrom) : $t('No limit') }} -
                  {{ period.validTo ? dateTime(period.validTo) : $t('No limit') }}
                </span>
                <div class="energy-cost-print-components">
                  <div v-for="(component, componentIndex) in period.components || []" :key="componentIndex">
                    <strong>{{ $t(componentLabels[component.kind] || component.kind) }}</strong>
                    <span v-for="detail in pricingDetails(component)" :key="`${detail.period?.validFrom || ''}-${detail.label}`">
                      <small v-if="detail.period">{{ detail.period.validFrom || $t('No limit') }} - {{ detail.period.validTo || $t('No limit') }}: </small>
                      {{ detail.label }}: {{ detail.value }}<small v-if="detail.unit"> {{ detail.unit }}</small
                      ><small> ({{ $t(detail.overridden ? 'Custom' : 'Preset default') }})</small>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </header>
          <div class="energy-cost-print-action text-right mb-3">
            <button
              type="button"
              class="btn btn-default"
              :disabled="loading || chartWorking || chartRendering || heatmapRendering || presetDetailsLoading"
              @click="printReport"
            >
              {{ $t('Print report') }}
            </button>
          </div>
          <energy-cost-summary :result="result" />
          <section class="energy-cost-print-chart">
            <div class="clearfix">
              <h3 class="pull-left">{{ $t('Gross usage-based cost over time') }}</h3>
              <div class="form-inline pull-right">
                <label class="mr-2">{{ $t('Aggregation') }}</label
                ><select v-model="granularity" class="form-control">
                  <option value="hour">{{ $t('Hour') }}</option>
                  <option value="day">{{ $t('Day') }}</option>
                  <option value="month">{{ $t('Month') }}</option>
                </select>
              </div>
            </div>
            <energy-cost-chart
              :buckets="buckets"
              :energy-buckets="energyBuckets"
              :currency="result.currency"
              :timezone="timezone"
              :granularity="chartGranularity || granularity"
              @rendering="chartRendering = $event"
            />
          </section>
          <section class="energy-cost-print-chart energy-cost-interactive-heatmap">
            <div class="clearfix">
              <h3 class="pull-left">{{ $t(heatmapMetric === 'usage' ? 'Energy usage by weekday and hour' : 'Gross cost by weekday and hour') }}</h3>
              <div class="btn-group pull-right">
                <button type="button" class="btn btn-default" :class="{active: heatmapMetric === 'cost'}" @click="heatmapMetric = 'cost'">
                  {{ $t('Total cost') }}
                </button>
                <button type="button" class="btn btn-default" :class="{active: heatmapMetric === 'costPerKwh'}" @click="heatmapMetric = 'costPerKwh'">
                  {{ $t('Cost per kWh') }}
                </button>
                <button type="button" class="btn btn-default" :class="{active: heatmapMetric === 'usage'}" @click="heatmapMetric = 'usage'">
                  {{ $t('Energy usage') }}
                </button>
              </div>
            </div>
            <p class="text-muted">
              {{
                heatmapMetric === 'cost'
                  ? $t('When did I spend the most?')
                  : heatmapMetric === 'usage'
                    ? $t('When did I use the most energy?')
                    : $t('When is electricity intrinsically most expensive?')
              }}
            </p>
            <energy-cost-heatmap
              :buckets="heatmapBuckets"
              :energy-buckets="heatmapEnergyBuckets"
              :currency="result.currency"
              :metric="heatmapMetric"
              @rendering="heatmapRendering = $event"
            />
          </section>
          <div class="energy-cost-print-heatmaps" aria-hidden="true">
            <section v-for="heatmap in printHeatmapMetrics" :key="heatmap.metric" class="energy-cost-print-chart">
              <h3>{{ $t(heatmap.title) }}</h3>
              <p class="text-muted">{{ $t(heatmap.description) }}</p>
              <energy-cost-heatmap :buckets="heatmapBuckets" :energy-buckets="heatmapEnergyBuckets" :currency="result.currency" :metric="heatmap.metric" />
            </section>
          </div>
          <energy-cost-breakdown :result="result" :currency="result.currency" />
          <energy-cost-details :result="result" :timezone="timezone" @align-billing-periods="alignRangeWithBillingPeriods" />
          <footer class="energy-cost-print-footer">{{ $t('Generated') }}: {{ dateTime(reportGeneratedAt) }}</footer>
        </template>
      </template>
      <div v-else-if="loading" class="well text-center">{{ $t('Calculating costs...') }}</div>
    </loading-cover>
  </section>
</template>

<style lang="scss">
  @use './energy-cost-print';
</style>

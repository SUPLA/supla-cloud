<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import {computed, ref, watch, onBeforeUnmount} from 'vue';
  import {useI18n} from 'vue-i18n';
  import {DateTime} from 'luxon';
  import {energyCostApi} from '@/api/energy-cost-api';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';
  import EnergyCostTariffPicker from './energy-cost-tariff-picker.vue';
  import EnergyCostComparisonChart from './energy-cost-comparison-chart.vue';
  import EnergyCostComparisonDetails from './energy-cost-comparison-details.vue';
  import {presentCostPlan, tariffCatalogPresentation} from './energy-cost-plan-presentation';
  import {comparisonConfiguration, compareCalculations, comparisonBuckets} from './energy-cost-comparison-utils';
  import {
    allowsHourlyAggregation,
    formatDecimal,
    grossCostLabel,
    incompleteFixedChargePeriods,
    periodicCostsUnavailable,
    preferredGranularity,
  } from './energy-cost-result-utils';

  const props = defineProps({
    channel: Object,
    plan: Object,
    range: Object,
    baseline: Object,
    baselineLoading: Boolean,
    baselineError: String,
    selectedTariffId: {type: String, default: ''},
    tariffSuggestions: {type: Array, default: () => []},
  });
  const emit = defineEmits([
    'update:periodicCostsUnavailable',
    'update:incompleteFixedChargePeriods',
    'update:selectedTariffId',
    'update:tariffSuggestions',
    'update:loading',
  ]);
  const store = useEnergyCostStore();
  const {t, locale} = useI18n();
  const selectedId = computed({get: () => props.selectedTariffId, set: (id) => emit('update:selectedTariffId', id)});
  const starter = ref(null);
  const alternative = ref(null);
  watch(alternative, (result) => emit('update:periodicCostsUnavailable', periodicCostsUnavailable(result)), {immediate: true});
  watch(alternative, (result) => emit('update:incompleteFixedChargePeriods', incompleteFixedChargePeriods(result)), {immediate: true});
  const loading = ref(false);
  const error = ref('');
  const catalogError = ref('');
  const suggesting = ref(false);
  const suggestions = ref(props.tariffSuggestions);
  watch(
    () => props.tariffSuggestions,
    (results) => (suggestions.value = results)
  );
  watch(suggestions, (results) => emit('update:tariffSuggestions', results));
  const suggestionError = ref('');
  let suggestionVersion = 0;
  const suggestionAllowed = computed(() => {
    const from = DateTime.fromISO(props.range.from, {zone: props.plan.configuration.timezone});
    const to = DateTime.fromISO(props.range.to, {zone: props.plan.configuration.timezone});
    return from.isValid && to.isValid && to > from && to <= from.plus({days: 31});
  });
  const suggestionTooltip = computed(() =>
    suggestionAllowed.value ? t('Suggest the cheapest') : t('Tariff suggestions are available for periods of up to 31 days.')
  );
  const suggestionCostLabels = computed(() =>
    Object.fromEntries(
      suggestions.value.map((result) => [
        result.id,
        result.total === null ? t('Cost unavailable') : `${money(result.total, result.currency)}${result.eligible ? '' : ` (${t('Not comparable')})`}`,
      ])
    )
  );
  watch(
    [() => props.range, () => props.plan.configuration, () => props.channel.id],
    () => {
      suggestionVersion++;
      suggesting.value = false;
      suggestions.value = [];
      suggestionError.value = '';
    },
    {deep: true}
  );
  async function suggestTariff() {
    if (!suggestionAllowed.value || busy.value) return;
    const token = ++suggestionVersion;
    suggesting.value = true;
    suggestionError.value = '';
    try {
      const result = await energyCostApi.suggestTariff(
        props.channel.id,
        Math.floor(Date.parse(props.range.from) / 1000),
        Math.floor(Date.parse(props.range.to) / 1000),
        props.plan.configuration
      );
      if (token !== suggestionVersion) return;
      suggestions.value = result.tariffs;
      if (result.cheapestId) selectedId.value = result.cheapestId;
      else suggestionError.value = 'No tariff can be reliably compared for the selected period.'; // i18n
    } catch {
      if (token === suggestionVersion) suggestionError.value = 'Could not suggest a tariff for the selected period.'; // i18n
    } finally {
      if (token === suggestionVersion) suggesting.value = false;
    }
  }
  let version = 0;
  store.fetchTariffs().catch(() => {
    catalogError.value = 'Could not load tariff plans.';
  }); // i18n
  const presentation = computed(() =>
    presentCostPlan(props.plan.configuration, {
      starters: Object.values(store.tariffDetailsById),
      presets: store.presets,
      t,
      locale: locale.value,
    })
  );
  const starterLabel = computed(() => (starter.value ? tariffCatalogPresentation(starter.value, t).label : t('Choose tariff')));
  const comparison = computed(() => compareCalculations(props.baseline, alternative.value, props.range));
  const noLogs = computed(() => props.baseline?.processedDeltaCount === 0 || alternative.value?.processedDeltaCount === 0);
  const baselineTotal = computed(() => props.baseline?.reportSummary?.costs?.gross?.total ?? props.baseline?.costs?.gross?.total ?? null);
  const alternativeTotal = computed(() => alternative.value?.reportSummary?.costs?.gross?.total ?? alternative.value?.costs?.gross?.total ?? null);
  const requestLoading = computed(() => loading.value || suggesting.value);
  watch(requestLoading, (value) => emit('update:loading', value), {immediate: true});
  const busy = computed(() => requestLoading.value || props.baselineLoading);
  const evaluated = computed(() => !busy.value && comparison.value.comparable);
  const granularity = ref('day');
  const hourlyAggregationAllowed = computed(() => allowsHourlyAggregation(props.range.from, props.range.to, props.plan.configuration.timezone));
  watch(
    [() => props.range, () => props.plan.configuration.timezone],
    () => {
      granularity.value = preferredGranularity(props.range.from, props.range.to, props.plan.configuration.timezone);
    },
    {immediate: true, deep: true}
  );
  const buckets = computed(() =>
    evaluated.value ? comparisonBuckets(props.baseline, alternative.value, granularity.value, props.plan.configuration.timezone) : []
  );
  const money = (value, currency = props.baseline?.currency || props.plan.configuration.currency) =>
    value === null || value === undefined ? '—' : `${formatDecimal(value)} ${currency}`;
  const date = (value) => DateTime.fromISO(value, {setZone: true}).setZone(props.plan.configuration.timezone).toFormat('dd LLL yyyy HH:mm');
  const differs = (result) =>
    result && (Date.parse(result.range.from) !== Date.parse(props.range.from) || Date.parse(result.range.to) !== Date.parse(props.range.to));
  const scenarios = computed(() => [
    {label: t('Current plan'), result: props.baseline},
    {label: starterLabel.value, result: alternative.value},
  ]);
  const detailsTab = ref('current');
  const detailsResult = computed(() => (detailsTab.value === 'current' ? props.baseline : alternative.value));
  watch(
    [selectedId, () => props.range, () => props.plan, () => props.channel.id],
    async () => {
      const token = ++version;
      alternative.value = null;
      starter.value = null;
      error.value = '';
      loading.value = false;
      if (!selectedId.value) return;
      loading.value = true;
      try {
        const nextStarter = await store.fetchTariff(selectedId.value);
        if (token !== version) return;
        starter.value = nextStarter;
        const nextConfiguration = comparisonConfiguration(nextStarter, props.plan.configuration);
        const result = await energyCostApi.simulate(
          props.channel.id,
          Math.floor(Date.parse(props.range.from) / 1000),
          Math.floor(Date.parse(props.range.to) / 1000),
          nextConfiguration
        );
        if (token === version) alternative.value = result;
      } catch (reason) {
        if (token === version) {
          error.value =
            reason.status === 400
              ? 'This tariff is incompatible with the meter configuration.' // i18n
              : reason.status === 404
                ? 'The selected tariff is no longer available.' // i18n
                : 'This tariff could not be calculated for the selected period.'; // i18n
        }
      } finally {
        if (token === version) loading.value = false;
      }
    },
    {deep: true, immediate: true}
  );
  onBeforeUnmount(() => {
    version++;
    suggestionVersion++;
    emit('update:loading', false);
  });
</script>
<template>
  <section class="energy-cost-comparison">
    <p v-if="catalogError" class="text-danger">{{ $t(catalogError) }}</p>
    <div class="row energy-cost-scenarios">
      <div class="col-sm-6">
        <section class="well energy-cost-scenario">
          <h3>{{ $t('Current plan') }}</h3>
          <span class="label label-success">{{ $t('Currently assigned') }}</span>
          <p>{{ presentation.label }}</p>
          <ul v-if="presentation.periodCount > 1">
            <li v-for="(period, index) in presentation.history" :key="index">{{ period.label }} — {{ period.effectiveDates }}</li>
          </ul>
          <p v-if="baselineError" class="text-danger">{{ $t(baselineError) }}</p>
          <div v-if="!noLogs" class="energy-cost-scenario-price">
            <strong>{{ baselineLoading ? '—' : money(baselineTotal) }}</strong>
            <small>{{ $t(grossCostLabel(baseline)) }}</small>
          </div>
        </section>
      </div>
      <div class="col-sm-6">
        <section class="well energy-cost-scenario">
          <div class="energy-cost-alternative-header">
            <h3>{{ $t('Alternative tariff') }}</h3>
            <span :title="suggestionTooltip" :tabindex="!suggestionAllowed || busy ? 0 : undefined">
              <button
                type="button"
                class="btn btn-default energy-cost-suggest"
                :disabled="!suggestionAllowed || busy"
                :aria-label="suggestionTooltip"
                :aria-busy="suggesting"
                @click="suggestTariff"
              >
                <fa icon="wand-magic-sparkles" />
              </button>
            </span>
          </div>
          <span class="label label-info">{{ $t('Simulation') }}</span>
          <energy-cost-tariff-picker
            v-model="selectedId"
            :tariffs="store.tariffs"
            :cost-labels="suggestionCostLabels"
            :disabled="busy"
            id-prefix="energy-cost-comparison"
            hide-label
          />
          <p v-if="suggestionError" class="text-danger mt-2" role="alert">{{ $t(suggestionError) }}</p>
          <div v-if="!noLogs" class="energy-cost-scenario-price">
            <strong>{{ money(alternativeTotal, alternative?.currency) }}</strong>
            <small>{{ $t(grossCostLabel(alternative)) }}</small>
          </div>
        </section>
      </div>
    </div>
    <p v-if="busy" role="status">{{ $t('Calculating costs...') }}</p>
    <p v-if="error" class="alert alert-danger">{{ $t(error) }}</p>
    <template v-if="evaluated">
      <h3>
        {{
          $t(
            Number(comparison.savings) > 0
              ? 'The {tariff} is cheaper.'
              : Number(comparison.savings) < 0
                ? 'The {tariff} is more expensive.'
                : '{tariff} costs the same as the current plan.',
            {tariff: starterLabel}
          )
        }}
      </h3>
      <p class="lead">
        {{ $t('Savings') }}: {{ money(comparison.savings) }} <span v-if="comparison.percentage !== null">({{ formatDecimal(comparison.percentage) }}%)</span>
      </p>
      <p>
        {{
          $t(
            comparison.full
              ? 'Comparison of total gross costs, including fixed charges.'
              : 'Comparison of consumption-dependent costs only. Fixed charges are not included.'
          )
        }}
      </p>
      <div class="clearfix">
        <h3 class="pull-left">{{ $t('Gross usage-based cost over time') }}</h3>
        <div class="form-inline pull-right">
          <label class="mr-2">{{ $t('Aggregation') }}</label
          ><select v-model="granularity" class="form-control">
            <option v-if="hourlyAggregationAllowed" value="hour">{{ $t('Hour') }}</option>
            <option value="day">{{ $t('Day') }}</option>
            <option value="month">{{ $t('Month') }}</option>
          </select>
        </div>
      </div>
      <energy-cost-comparison-chart
        :buckets="buckets"
        :currency="baseline.currency"
        :timezone="plan.configuration.timezone"
        :alternative-label="starterLabel"
      />
      <section>
        <h3>{{ $t('Cost breakdown') }}</h3>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th></th>
                <th>{{ $t('Current plan') }}</th>
                <th>{{ starterLabel }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="[key, label] in [
                  ['usageBased', 'Usage-based cost'],
                  ['periodic', 'Fixed charges'],
                  ['total', 'Total gross cost'],
                ]"
                :key="key"
              >
                <th>{{ $t(label) }}</th>
                <td v-for="scenario in scenarios" :key="scenario.label">
                  {{ money(key === 'total' ? scenario.result.costs.gross.total : scenario.result.costs.gross[key]?.total) }}
                </td>
              </tr>
              <tr>
                <th>{{ $t('Savings') }}</th>
                <td colspan="2">{{ money(comparison.savings) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>
    <p v-else-if="!busy && selectedId && baseline && alternative" class="alert alert-info">{{ $t(comparison.reason) }}</p>
    <section v-if="baseline && alternative && !busy && !noLogs" class="energy-cost-comparison-report mt-4">
      <ul class="nav nav-tabs mb-3" role="tablist">
        <li :class="{active: detailsTab === 'current'}" role="presentation">
          <a href="#" role="tab" :aria-selected="detailsTab === 'current'" @click.prevent="detailsTab = 'current'">{{ $t('Current plan') }}</a>
        </li>
        <li :class="{active: detailsTab === 'alternative'}" role="presentation">
          <a href="#" role="tab" :aria-selected="detailsTab === 'alternative'" @click.prevent="detailsTab = 'alternative'">{{ starterLabel }}</a>
        </li>
      </ul>
      <div role="tabpanel">
        <energy-cost-comparison-details :result="detailsResult" :timezone="plan.configuration.timezone" />
      </div>
    </section>
    <template v-for="scenario in noLogs ? [] : scenarios" :key="scenario.label">
      <p v-if="differs(scenario.result)">
        {{ scenario.label }} — {{ $t('Effective calculation range') }}: {{ date(scenario.result.range.from) }} – {{ date(scenario.result.range.to) }}
      </p>
      <details v-if="scenario.result?.warnings?.length">
        <summary>{{ scenario.label }} — {{ $t('Warnings') }}</summary>
        <ul>
          <li v-for="(warning, index) in scenario.result.warnings" :key="index">{{ date(warning.from) }} – {{ date(warning.to) }}: {{ warning.message }}</li>
        </ul>
      </details>
    </template>
  </section>
</template>

<style scoped>
  .energy-cost-alternative-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
  }

  .energy-cost-alternative-header > h3 {
    margin-top: 0;
  }

  .energy-cost-suggest:disabled {
    pointer-events: none;
  }

  .energy-cost-scenarios {
    display: flex;
    flex-wrap: wrap;
  }

  .energy-cost-scenarios > .col-sm-6 {
    display: flex;
    width: 100%;
  }

  @media (min-width: 768px) {
    .energy-cost-scenarios > .col-sm-6 {
      width: 50%;
    }
  }

  .energy-cost-scenario {
    display: flex;
    flex-direction: column;
    width: 100%;
  }

  .energy-cost-scenario-price {
    margin-top: auto;
    padding-top: 10px;
  }

  .energy-cost-scenario-price > strong {
    display: block;
    font-size: 2em;
  }

  .energy-cost-scenario-price > small {
    display: block;
  }

  .energy-cost-scenario > h3 {
    margin-top: 0;
  }

  .energy-cost-scenario > .label {
    display: inline-block;
    align-self: flex-start;
    margin-bottom: 10px;
  }
</style>

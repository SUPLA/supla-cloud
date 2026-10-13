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
  import DialogWindow from '@/common/gui/dialog/DialogWindow.vue';
  import DialogContent from '@/common/gui/dialog/DialogContent.vue';
  import {presentCostPlan, tariffCatalogPresentation} from './energy-cost-plan-presentation';
  import {comparisonConfiguration, compareCalculations, comparisonBuckets} from './energy-cost-comparison-utils';
  import {formatDecimal, preferredGranularity} from './energy-cost-result-utils';

  const props = defineProps({channel: Object, plan: Object, range: Object, baseline: Object, baselineLoading: Boolean, baselineError: String});
  const emit = defineEmits(['applied']);
  const store = useEnergyCostStore();
  const {t, locale} = useI18n();
  const selectedId = ref('');
  const starter = ref(null);
  const configuration = ref(null);
  const alternative = ref(null);
  const loading = ref(false);
  const error = ref('');
  const catalogError = ref('');
  const confirming = ref(false);
  const applying = ref(false);
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
  const busy = computed(() => loading.value || props.baselineLoading);
  const evaluated = computed(() => !busy.value && comparison.value.comparable);
  const buckets = computed(() =>
    evaluated.value
      ? comparisonBuckets(
          props.baseline,
          alternative.value,
          preferredGranularity(props.range.from, props.range.to, props.plan.configuration.timezone),
          props.plan.configuration.timezone
        )
      : []
  );
  const money = (value, currency = props.baseline?.currency || props.plan.configuration.currency) =>
    value === null || value === undefined ? '—' : `${formatDecimal(value)} ${currency}`;
  const date = (value) => DateTime.fromISO(value, {setZone: true}).setZone(props.plan.configuration.timezone).toFormat('dd LLL yyyy HH:mm');
  const differs = (result) =>
    result && (Date.parse(result.range.from) !== Date.parse(props.range.from) || Date.parse(result.range.to) !== Date.parse(props.range.to));
  const scenarios = computed(() => [
    {label: t('Current plan'), result: props.baseline},
    {label: t('Alternative tariff'), result: alternative.value},
  ]);
  watch(
    [selectedId, () => props.range, () => props.plan, () => props.channel.id],
    async () => {
      const token = ++version;
      alternative.value = null;
      starter.value = null;
      configuration.value = null;
      confirming.value = false;
      error.value = '';
      loading.value = false;
      if (!selectedId.value) return;
      loading.value = true;
      try {
        const nextStarter = await store.fetchTariff(selectedId.value);
        if (token !== version) return;
        starter.value = nextStarter;
        const nextConfiguration = comparisonConfiguration(nextStarter, props.plan.configuration);
        configuration.value = nextConfiguration;
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
    {deep: true}
  );
  async function apply(dialog) {
    if (!evaluated.value || applying.value) return dialog.setLoading(false);
    applying.value = true;
    error.value = '';
    try {
      await store.assignStarter(props.channel.id, selectedId.value, configuration.value);
      confirming.value = false;
      emit('applied');
    } catch (reason) {
      error.value = reason.body?.message || 'Could not apply the tariff.'; // i18n
    } finally {
      applying.value = false;
      dialog.setLoading(false);
    }
  }
  onBeforeUnmount(() => version++);
</script>
<template>
  <section class="energy-cost-comparison">
    <p v-if="catalogError" class="text-danger">{{ $t(catalogError) }}</p>
    <fieldset :disabled="applying">
      <energy-cost-tariff-picker v-model="selectedId" :tariffs="store.tariffs" id-prefix="energy-cost-comparison" />
    </fieldset>
    <div class="row">
      <div class="col-sm-6">
        <section class="well">
          <h3>{{ $t('Current plan') }}</h3>
          <span class="label label-success">{{ $t('Currently assigned') }}</span>
          <p>{{ presentation.label }}</p>
          <ul v-if="presentation.periodCount > 1">
            <li v-for="(period, index) in presentation.history" :key="index">{{ period.label }} — {{ period.effectiveDates }}</li>
          </ul>
          <strong>{{ baselineLoading ? '—' : money(evaluated ? comparison.current : baseline?.costs?.gross?.usageBased?.total) }}</strong>
          <small v-if="baseline && !evaluated"> {{ $t('Usage-based cost') }}</small>
          <p v-if="baselineError" class="text-danger">{{ $t(baselineError) }}</p>
        </section>
      </div>
      <div class="col-sm-6">
        <section class="well">
          <h3>{{ $t('Alternative tariff') }}</h3>
          <span class="label label-info">{{ $t('Simulation') }}</span>
          <p>{{ starterLabel }}</p>
          <strong>{{ money(evaluated ? comparison.alternative : alternative?.costs?.gross?.usageBased?.total, alternative?.currency) }}</strong>
          <small v-if="alternative && !evaluated"> {{ $t('Usage-based cost') }}</small>
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
              ? 'The alternative tariff is cheaper.'
              : Number(comparison.savings) < 0
                ? 'The alternative tariff is more expensive.'
                : 'Both tariffs cost the same.'
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
      <h3>{{ $t('Gross usage-based cost over time') }}</h3>
      <energy-cost-comparison-chart :buckets="buckets" :currency="baseline.currency" :timezone="plan.configuration.timezone" />
      <details>
        <summary>{{ $t('Cost breakdown') }}</summary>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th></th>
                <th>{{ $t('Current plan') }}</th>
                <th>{{ $t('Alternative tariff') }}</th>
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
      </details>
      <button class="btn btn-default mt-3" :disabled="applying" @click="confirming = true">{{ $t('Apply this tariff to the meter') }}</button>
    </template>
    <p v-else-if="!busy && selectedId && baseline && alternative" class="alert alert-info">{{ $t(comparison.reason) }}</p>
    <p v-if="scenarios.some(({result}) => result && !result.billingContext?.requestedRangeCoversWholePeriods)" class="text-muted">
      {{ $t('The selected range does not cover complete billing periods.') }}
    </p>
    <template v-for="scenario in scenarios" :key="scenario.label">
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
    <dialog-window v-model="confirming" cancellable :close-on-backdrop="false" @confirm="apply">
      <dialog-content
        ><template #header
          ><h4>{{ $t('Apply this tariff to the meter') }}</h4></template
        >
        <p>{{ starterLabel }}</p>
        <p>
          {{
            $t(
              'The existing tariff history of this meter will be replaced. Historical cost calculations will use the newly assigned tariff. Other meters will keep their current plans.'
            )
          }}
        </p>
        <p v-if="error" class="text-danger">{{ $t(error) }}</p>
      </dialog-content>
    </dialog-window>
  </section>
</template>

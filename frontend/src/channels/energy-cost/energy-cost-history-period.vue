<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import {computed, ref} from 'vue';
  import {useI18n} from 'vue-i18n';
  import {DateTime} from 'luxon';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';
  import EnergyCostTariffPicker from './energy-cost-tariff-picker.vue';
  import EnergyCostCustomComposer from './energy-cost-custom-composer.vue';
  import EnergyCostPlanPeriod from './energy-cost-plan-period.vue';
  import {cloneTariffComponents, dateFromDatetime, dateToDatetime} from './energy-cost-plan-utils';
  import {changeTransition, copyDraft, hasPriceOverrides, timelineError} from './energy-cost-history';
  import {compatibleComponents} from './energy-cost-composer';
  import {presentTariffPeriod} from './energy-cost-plan-presentation';

  const props = defineProps({period: Object, configuration: Object, errors: Object, index: Number});
  const emit = defineEmits(['update:period', 'remove']);
  const store = useEnergyCostStore();
  const {t, locale} = useI18n();
  const changing = ref(!props.period.components.length);
  const custom = ref(!props.period.components.length);
  const advanced = ref(false);
  const error = ref('');
  const dateError = ref('');
  const draft = ref(copyDraft(props.period));
  const fromDate = ref(dateFromDatetime(props.period.validFrom, props.configuration.timezone));
  const selectedStarter = ref('');
  const busy = ref(false);
  let selectionVersion = 0;
  const summary = computed(() => presentTariffPeriod(props.period, {starters: Object.values(store.tariffDetailsById), presets: store.presets, t}));
  function formatDate(value, end = false) {
    if (!value) return '';
    let date = DateTime.fromISO(value).setZone(props.configuration.timezone).setLocale(locale.value);
    // Midnight ends are exclusive; display the last applicable calendar day.
    if (end && date.equals(date.startOf('day'))) date = date.minus({days: 1});
    return date.toLocaleString({year: 'numeric', month: '2-digit', day: '2-digit'});
  }
  const fromLabel = computed(() => formatDate(props.period.validFrom));
  const toLabel = computed(() => formatDate(props.period.validTo, true));

  function edit() {
    selectionVersion++;
    draft.value = copyDraft(props.period);
    fromDate.value = dateFromDatetime(props.period.validFrom, props.configuration.timezone);
    selectedStarter.value = '';
    error.value = '';
    dateError.value = '';
    busy.value = false;
    custom.value = !props.period.components.length;
    advanced.value = false;
    changing.value = true;
  }
  function cancel() {
    selectionVersion++;
    busy.value = false;
    changing.value = false;
    error.value = '';
    dateError.value = '';
  }
  function save() {
    if (busy.value || error.value) return;
    dateError.value =
      props.index > 0 ? timelineError(changeTransition(props.configuration.periods, props.index, fromDate.value, props.configuration.timezone)) : '';
    if (dateError.value) return;
    if (!draft.value.components.length) {
      error.value = 'Choose a compatible tariff.'; // i18n
      return;
    }
    if (
      hasPriceOverrides(props.period) &&
      JSON.stringify(props.period.components) !== JSON.stringify(draft.value.components) &&
      !window.confirm(t('Changing the tariff will reset custom pricing settings for this period.'))
    )
      return;
    emit('update:period', {
      ...copyDraft(draft.value),
      validFrom: props.index > 0 ? dateToDatetime(fromDate.value, props.configuration.timezone) : props.period.validFrom,
    });
    cancel();
  }

  async function selectStarter(id) {
    if (!id) return;
    const version = ++selectionVersion;
    selectedStarter.value = id;
    busy.value = true;
    try {
      error.value = '';
      const starter = await store.fetchTariff(id);
      const components = cloneTariffComponents(starter);
      await Promise.all(components.map((component) => store.fetchPreset(component.presetId)));
      if (version !== selectionVersion) return;
      if (!compatibleComponents(components, props.configuration, store.presetDetailsById)) throw new Error('Choose a compatible tariff.'); // i18n
      draft.value = {...draft.value, components};
      custom.value = false;
    } catch (reason) {
      if (version === selectionVersion) error.value = reason.body?.message || reason.message;
    } finally {
      if (version === selectionVersion) busy.value = false;
    }
  }
  function updateCustom(period) {
    selectionVersion++;
    busy.value = false;
    draft.value = period;
    selectedStarter.value = '';
    error.value = '';
  }
</script>
<template>
  <div>
    <div v-if="!changing" class="row">
      <div class="col-sm-8">
        <strong>{{ summary.label }}</strong>
        <small v-if="summary.customized"> · {{ $t('Customized prices') }}</small>
        <div v-if="index > 0">{{ $t('From date') }}: {{ fromLabel }}</div>
        <div v-if="index < configuration.periods.length - 1">{{ $t('To date') }}: {{ toLabel }}</div>
        <template v-if="summary.custom">
          <div v-if="summary.supplier">{{ $t('Electricity supply') }}: {{ summary.supplier }}</div>
          <div v-if="summary.distribution">{{ $t('Distribution') }}: {{ summary.distribution }}</div>
        </template>
        <energy-cost-plan-period
          v-if="advanced"
          :period="period"
          :index="index"
          :count="1"
          :errors="errors || {}"
          :prices-only="true"
          @update:period="emit('update:period', $event)"
        />
      </div>
      <div class="col-sm-4 period-actions">
        <button type="button" class="btn btn-link" @click="edit">{{ $t('Change tariff') }}</button>
        <button type="button" class="btn btn-link" @click="advanced = !advanced">{{ $t('Customize prices') }}</button>
        <button v-if="configuration.periods.length > 1" type="button" class="btn btn-link text-danger" @click="emit('remove')">
          {{ $t('Remove period') }}
        </button>
      </div>
    </div>
    <template v-if="changing">
      <energy-cost-tariff-picker :tariffs="store.tariffs" :model-value="selectedStarter" :id-prefix="`history-${index}`" @update:model-value="selectStarter" />
      <div v-if="index > 0" class="form-group">
        <label :for="`tariff-transition-${index}-date`">{{ $t('From date') }}</label>
        <input :id="`tariff-transition-${index}-date`" v-model="fromDate" type="date" class="form-control" :lang="locale" />
        <div v-if="dateError" class="text-danger">{{ $t(dateError) }}</div>
      </div>
      <button type="button" class="btn btn-link" @click="custom = !custom">{{ $t('Compose custom tariff') }}</button>
      <energy-cost-custom-composer v-if="custom" :period="draft" :configuration="configuration" @update:period="updateCustom" />
      <div class="edit-actions">
        <button type="button" class="btn btn-default" :disabled="busy" @click="save">{{ $t('OK') }}</button>
        <button type="button" class="btn btn-link" @click="cancel">{{ $t('Cancel') }}</button>
      </div>
    </template>
    <div v-if="errors?.components?.some((component) => Object.keys(component).length)" class="text-danger">
      {{ $t('Complete the tariff configuration or customize its prices.') }}
    </div>
    <div v-if="error" class="text-danger">{{ $t(error) }}</div>
    <div v-if="errors?.billingCycles" class="text-danger">{{ $t(errors.billingCycles) }}</div>
  </div>
</template>
<style scoped>
  .period-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
  }
  @media (min-width: 768px) {
    .period-actions {
      align-items: flex-end;
    }
  }
  .edit-actions {
    margin-top: 15px;
  }
</style>

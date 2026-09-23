<script setup>
  import {computed, nextTick, ref, watch} from 'vue';
  import {storeToRefs} from 'pinia';
  import DialogWindow from '@/common/gui/dialog/DialogWindow.vue';
  import DialogContent from '@/common/gui/dialog/DialogContent.vue';
  import FormButton from '@/common/gui/FormButton.vue';
  import AccordionItem from '@/common/gui/accordion/accordion-item.vue';
  import AccordionRoot from '@/common/gui/accordion/accordion-root.vue';
  import EnergyCostPlanPeriod from './energy-cost-plan-period.vue';
  import {
    dateFromDatetime,
    dateFromPeriodEnd,
    dateToDatetime,
    dateToPeriodEnd,
    fromDatetimeLocal,
    inputsForComponent,
    isDecimal,
    isInteger,
    isTime,
    normalizeDecimal,
    presetDefault,
    serializeConfiguration,
    shiftDatetimeDays,
  } from './energy-cost-plan-utils';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';

  const props = defineProps({modelValue: Boolean, plan: Object, channelId: Number});
  const emit = defineEmits(['update:modelValue', 'saved', 'deleted']);
  const opened = computed({get: () => props.modelValue, set: (value) => emit('update:modelValue', value)});
  const store = useEnergyCostStore();
  const {presets, presetDetailsById} = storeToRefs(store);
  const firstDayOfMonth = () => new Date().toISOString().slice(0, 8) + '01';
  const defaultConfiguration = () => ({
    version: 2,
    currency: 'PLN',
    timezone: 'Europe/Warsaw',
    priceBasis: 'NET',
    billingCycles: [{anchor: firstDayOfMonth(), length: 1, unit: 'MONTH'}],
    periods: [
      {
        validFrom: '',
        validTo: '',
        components: [],
      },
    ],
  });
  const name = ref('');
  const configuration = ref(defaultConfiguration());
  const errors = ref({name: '', billingCycles: [], periods: []});
  const serverError = ref(null);
  const saving = ref(false);
  const deleting = ref(false);
  const deleteConfirmation = ref(false);
  const createdPlan = ref(null);
  const openedPeriod = ref('period-0');
  const periods = computed(() => configuration.value.periods);

  async function resetDraft() {
    name.value = props.plan?.name || '';
    configuration.value = JSON.parse(JSON.stringify(props.plan?.configuration || defaultConfiguration()));
    errors.value = {name: '', billingCycles: [], periods: []};
    openedPeriod.value = props.plan ? null : 'period-0';
    serverError.value = null;
    createdPlan.value = null;
    deleteConfirmation.value = false;
    await nextTick();
  }

  watch(
    opened,
    async (isOpened) => {
      if (!isOpened) return;
      await resetDraft();
      try {
        await store.fetchPresets();
        await Promise.all(
          periods.value.flatMap((period) =>
            period.components.filter((component) => component.presetId).map((component) => store.fetchPreset(component.presetId))
          )
        );
      } catch (error) {
        serverError.value = error.body?.message || 'Could not load tariff presets.'; // i18n
      }
    },
    {immediate: true}
  );

  function componentPresetIndex(component, preset) {
    return preset?.document.billingDefinitionTemplate?.periods?.[0]?.components?.findIndex((item) => item.id === component.componentId) ?? -1;
  }

  function validateComponent(component, componentErrors) {
    if (component.presetId === undefined) {
      if (!isDecimal(component.rate)) componentErrors.rate = 'Invalid decimal value.'; // i18n
      return;
    }
    const preset = presetDetailsById.value[component.presetId];
    const componentIndex = componentPresetIndex(component, preset);
    if (!component.presetId || !preset || componentIndex < 0) {
      componentErrors.preset = 'Choose a compatible tariff.'; // i18n
      return;
    }
    inputsForComponent(preset, componentIndex).forEach((input) => {
      const value = Object.hasOwn(component.values, input.id) ? component.values[input.id] : presetDefault(preset, input, componentIndex);
      if (input.required && (value === null || value === undefined || value === ''))
        componentErrors[input.id] = 'This field is required.'; // i18n
      else if (value !== null && value !== undefined && value !== '') {
        if (input.type === 'DECIMAL' && !isDecimal(value)) componentErrors[input.id] = 'Invalid decimal value.'; // i18n
        if (input.type === 'INTEGER' && (!isInteger(value) || (input.minimum !== undefined && Number(value) < input.minimum)))
          componentErrors[input.id] = 'Invalid integer value.'; // i18n
        if (input.type === 'DATETIME' && !fromDatetimeLocal(value, preset.document.timezone) && !/Z|[+-]\d\d:\d\d$/.test(value))
          componentErrors[input.id] = 'Invalid date and time.'; // i18n
        if (input.type === 'TIME' && !isTime(value)) componentErrors[input.id] = 'Invalid time.'; // i18n
      }
    });
  }

  function validate() {
    const result = {
      name: '',
      billingCycles: configuration.value.billingCycles.map(() => ({})),
      periods: periods.value.map((period) => ({components: period.components.map(() => ({}))})),
    };
    if (!name.value.trim()) result.name = 'Plan name is required.'; // i18n
    configuration.value.billingCycles.forEach((cycle, index) => {
      if (!/^\d{4}-\d{2}-\d{2}$/.test(cycle.anchor || '')) result.billingCycles[index].anchor = 'Anchor date is required.'; // i18n
      if (!isInteger(cycle.length) || Number(cycle.length) < 1) result.billingCycles[index].length = 'Length must be positive.'; // i18n
      if (cycle.validFrom && cycle.validTo && cycle.validFrom >= cycle.validTo) result.billingCycles[index].validTo = 'End date must be after start date.'; // i18n
    });
    periods.value.forEach((period, index) => {
      const periodErrors = result.periods[index];
      if (!period.validFrom) periodErrors.validFrom = 'Start date is required.'; // i18n
      if (!period.validTo) periodErrors.validTo = 'End date is required.'; // i18n
      if (period.validFrom && period.validTo && period.validFrom >= period.validTo) periodErrors.validTo = 'End date must be after start date.'; // i18n
      if (index < periods.value.length - 1 && period.validTo !== periods.value[index + 1].validFrom)
        periodErrors.validTo = 'The end date must match the next period start date.'; // i18n
      if (!period.components.length) periodErrors.components = [{preset: 'Add a price component.'}]; // i18n
      period.components.forEach((component, componentIndex) => validateComponent(component, periodErrors.components[componentIndex]));
    });
    errors.value = result;
    return (
      !result.name &&
      result.billingCycles.every((error) => Object.keys(error).length === 0) &&
      result.periods.every(
        (error) =>
          Object.keys(error).filter((key) => key !== 'components').length === 0 &&
          error.components.every((componentError) => Object.keys(componentError).length === 0)
      )
    );
  }

  function payload() {
    const periods = configuration.value.periods.map((period) => ({
      ...period,
      components: period.components.map((component) => {
        const values = {...component.values};
        const preset = presetDetailsById.value[component.presetId];
        const componentIndex = componentPresetIndex(component, preset);
        inputsForComponent(preset, componentIndex).forEach((input) => {
          if (input.type === 'DECIMAL' && Object.hasOwn(values, input.id)) values[input.id] = normalizeDecimal(values[input.id]);
          if (input.type === 'INTEGER' && Object.hasOwn(values, input.id) && isInteger(values[input.id])) values[input.id] = Number(values[input.id]);
        });
        return component.presetId === undefined ? {...component, rate: normalizeDecimal(component.rate)} : {...component, values};
      }),
    }));
    return {name: name.value.trim(), configuration: serializeConfiguration({...configuration.value, periods})};
  }

  function updatePeriod(index, period) {
    configuration.value.periods[index] = period;
  }
  function updateBoundary(index, boundary, date, timezone) {
    configuration.value.periods[index][boundary] = boundary === 'validTo' ? dateToPeriodEnd(date, timezone) : dateToDatetime(date, timezone);
    normalizePeriods(index, timezone);
  }
  function normalizePeriods(index, timezone) {
    for (let previousIndex = index - 1; previousIndex >= 0; previousIndex--) {
      const previous = periods.value[previousIndex];
      const next = periods.value[previousIndex + 1];
      previous.validTo = next.validFrom;
      if (previousIndex > 0 && previous.validFrom >= previous.validTo) previous.validFrom = shiftDatetimeDays(previous.validTo, timezone, -1);
    }
    for (let nextIndex = index + 1; nextIndex < periods.value.length; nextIndex++) {
      const previous = periods.value[nextIndex - 1];
      const next = periods.value[nextIndex];
      next.validFrom = previous.validTo;
      if (nextIndex < periods.value.length - 1 && next.validTo <= next.validFrom) next.validTo = shiftDatetimeDays(next.validFrom, timezone, 1);
    }
  }
  const periodTimezone = (period) =>
    period.components.map((component) => presetDetailsById.value[component.presetId]?.document.timezone).find(Boolean) || configuration.value.timezone;
  const periodHasErrors = (index) =>
    Object.keys(errors.value.periods[index] || {}).some((key) => key !== 'components') ||
    errors.value.periods[index]?.components?.some((component) => Object.keys(component).length);
  const periodLabel = (period) =>
    period.components.map((component) => presets.value.find((preset) => preset.id === component.presetId)?.label || component.kind).join(', ');
  function addPeriod() {
    const lastIndex = periods.value.length - 1;
    const lastPeriod = periods.value[lastIndex];
    const end = lastPeriod.validTo;
    const timezone = periodTimezone(lastPeriod);
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    const boundary = dateToDatetime(today, timezone);
    lastPeriod.validTo = boundary;
    configuration.value.periods.push({validFrom: boundary, validTo: end, components: []});
    openedPeriod.value = `period-${lastIndex + 1}`;
  }
  function removePeriod(index) {
    const lastIndex = periods.value.length - 1;
    if (index === 0) configuration.value.periods[1].validFrom = configuration.value.periods[0].validFrom;
    else if (index === lastIndex) configuration.value.periods[index - 1].validTo = configuration.value.periods[index].validTo;
    else configuration.value.periods[index - 1].validTo = configuration.value.periods[index + 1].validFrom;
    configuration.value.periods.splice(index, 1);
    openedPeriod.value = `period-${Math.max(0, index - 1)}`;
  }
  function addBillingCycle() {
    const lastCycle = configuration.value.billingCycles.at(-1);
    const boundary = dateToDatetime(firstDayOfMonth(), configuration.value.timezone);
    lastCycle.validTo = boundary;
    configuration.value.billingCycles.push({validFrom: boundary, anchor: firstDayOfMonth(), length: 1, unit: 'MONTH'});
  }
  function removeBillingCycle(index) {
    configuration.value.billingCycles.splice(index, 1);
  }
  function updateBillingBoundary(index, boundary, value) {
    configuration.value.billingCycles[index][boundary] = dateToDatetime(value, configuration.value.timezone);
  }

  async function save(dialog) {
    if (deleteConfirmation.value) return deletePlan(dialog);
    if (!validate()) return dialog?.setLoading?.(false);
    saving.value = true;
    serverError.value = null;
    try {
      const plan = createdPlan.value || (props.plan ? await store.updatePlan(props.plan.id, payload()) : await store.createPlan(payload()));
      createdPlan.value = plan;
      if (!props.plan && props.channelId) await store.assignPlan(props.channelId, plan.id);
      emit('saved', plan);
      opened.value = false;
    } catch (error) {
      serverError.value = createdPlan.value ? 'The cost plan was created, but could not be assigned.' : error.body?.message || 'Could not save the cost plan.'; // i18n
      dialog?.setLoading?.(false);
    } finally {
      saving.value = false;
    }
  }
  async function deletePlan(dialog) {
    deleting.value = true;
    serverError.value = null;
    try {
      await store.deletePlan(props.plan.id);
      emit('deleted', props.plan.id);
      opened.value = false;
    } catch (error) {
      serverError.value = error.body?.message || 'Could not delete the cost plan.';
    } finally {
      // i18n
      deleting.value = false;
      deleteConfirmation.value = false;
      dialog?.setLoading?.(false);
    }
  }
</script>

<template>
  <dialog-window v-model="opened" cancellable @confirm="save">
    <dialog-content container-class="dialog-800">
      <template #header
        ><h4>{{ $t(plan ? 'Edit cost plan' : 'Create cost plan') }}</h4></template
      >
      <template #default>
        <template v-if="deleteConfirmation"
          ><div class="alert alert-danger">{{ $t('Deleting this cost plan is irreversible and unassigns it from every electricity meter that uses it.') }}</div>
          <p>{{ $t('Confirm deletion of the {planName} cost plan.', {planName: plan.name}) }}</p></template
        >
        <template v-else>
          <div v-if="plan" class="alert alert-warning">{{ $t('Changes to this cost plan affect every electricity meter that uses it.') }}</div>
          <div class="form-group" :class="{'has-error': errors.name}">
            <label for="energy-cost-plan-name">{{ $t('Name') }}</label
            ><input id="energy-cost-plan-name" v-model="name" class="form-control" maxlength="255" /><span v-if="errors.name" class="help-block">{{
              $t(errors.name)
            }}</span>
          </div>
          <h5>{{ $t('Billing periods') }}</h5>
          <div v-for="(cycle, index) in configuration.billingCycles" :key="index" class="row energy-cost-billing-cycle">
            <div class="col-sm-3 form-group">
              <label>{{ $t('From date') }}</label
              ><input
                type="date"
                class="form-control"
                :value="dateFromDatetime(cycle.validFrom, configuration.timezone)"
                @input="updateBillingBoundary(index, 'validFrom', $event.target.value)"
              />
            </div>
            <div class="col-sm-3 form-group">
              <label>{{ $t('To date') }}</label
              ><input
                type="date"
                class="form-control"
                :value="dateFromDatetime(cycle.validTo, configuration.timezone)"
                @input="updateBillingBoundary(index, 'validTo', $event.target.value)"
              />
            </div>
            <div class="col-sm-2 form-group" :class="{'has-error': errors.billingCycles[index]?.anchor}">
              <label>{{ $t('Anchor date') }}</label
              ><input v-model="cycle.anchor" type="date" class="form-control" />
            </div>
            <div class="col-sm-2 form-group">
              <label>{{ $t('Length') }}</label
              ><input v-model="cycle.length" type="number" min="1" class="form-control" />
            </div>
            <div class="col-sm-2 form-group">
              <label>{{ $t('Unit') }}</label
              ><select v-model="cycle.unit" class="form-control">
                <option value="DAY">{{ $t('Day') }}</option>
                <option value="WEEK">{{ $t('Week') }}</option>
                <option value="MONTH">{{ $t('Month') }}</option>
                <option value="YEAR">{{ $t('Year') }}</option></select
              ><button v-if="configuration.billingCycles.length > 1" type="button" class="btn btn-link text-danger" @click="removeBillingCycle(index)">
                {{ $t('Remove') }}
              </button>
            </div>
          </div>
          <button type="button" class="btn btn-default" @click="addBillingCycle">{{ $t('Add billing period') }}</button>
          <h5>{{ $t('Price periods') }}</h5>
          <accordion-root v-model="openedPeriod"
            ><accordion-item v-for="(period, index) in periods" :key="index" :name="`period-${index}`" :error="periodHasErrors(index)"
              ><template #title
                >{{ periodLabel(period) }} ({{ period.validFrom ? dateFromDatetime(period.validFrom, periodTimezone(period)) : '...' }} -
                {{ period.validTo ? dateFromPeriodEnd(period.validTo, periodTimezone(period)) : '...' }})</template
              ><energy-cost-plan-period
                :period="period"
                :index="index"
                :count="periods.length"
                :errors="errors.periods[index] || {}"
                @update:period="updatePeriod(index, $event)"
                @update:boundary="(boundary, date, timezone) => updateBoundary(index, boundary, date, timezone)"
                @remove="removePeriod(index)" /></accordion-item
          ></accordion-root>
          <button type="button" class="btn btn-default" @click="addPeriod">{{ $t('Add period') }}</button>
          <div v-if="serverError" class="text-danger">{{ $t(serverError) }}</div>
        </template>
      </template>
      <template #footer
        ><template v-if="deleteConfirmation"
          ><form-button :loading="deleting" button-class="btn-danger" @click="deletePlan">{{ $t('Delete cost plan') }}</form-button
          ><button type="button" class="btn btn-default" :disabled="deleting" @click="deleteConfirmation = false">{{ $t('Cancel') }}</button></template
        ><template v-else
          ><button v-if="plan" type="button" class="btn btn-danger pull-left" :disabled="saving" @click="deleteConfirmation = true">{{ $t('Delete') }}</button
          ><form-button :loading="saving" button-class="btn-green" @click="save">{{ $t('Save changes') }}</form-button
          ><button type="button" class="btn btn-default" :disabled="saving" @click="opened = false">{{ $t('Cancel') }}</button></template
        ></template
      >
    </dialog-content>
  </dialog-window>
</template>

<style scoped>
  .energy-cost-billing-cycle {
    margin: 0;
    padding: 10px 0;
    border-bottom: 1px solid #ddd;
  }
</style>

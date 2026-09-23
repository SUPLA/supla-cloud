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
  const name = ref('');
  const configuration = ref({version: 1, entries: [{presetId: '', values: {}}]});
  const errors = ref({name: '', periods: []});
  const serverError = ref(null);
  const saving = ref(false);
  const deleting = ref(false);
  const deleteConfirmation = ref(false);
  const createdPlan = ref(null);
  const openedPeriod = ref('period-0');
  const entries = computed(() => configuration.value.entries);

  async function resetDraft() {
    name.value = props.plan?.name || '';
    configuration.value = JSON.parse(JSON.stringify(props.plan?.configuration || {version: 1, entries: [{presetId: '', values: {}}]}));
    errors.value = {name: '', periods: []};
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
        await Promise.all(entries.value.filter((entry) => entry.presetId).map((entry) => store.fetchPreset(entry.presetId)));
      } catch (error) {
        serverError.value = error.body?.message || 'Could not load tariff presets.'; // i18n
      }
    },
    {immediate: true}
  );

  function validate() {
    const result = {name: '', periods: entries.value.map(() => ({}))};
    if (!name.value.trim()) result.name = 'Plan name is required.'; // i18n
    entries.value.forEach((entry, index) => {
      const periodErrors = result.periods[index];
      const preset = presetDetailsById.value[entry.presetId];
      if (!entry.presetId || !preset) periodErrors.preset = 'Choose tariff.'; // i18n
      if (index > 0 && !entry.validFrom) periodErrors.validFrom = 'Start date is required.'; // i18n
      if (index < entries.value.length - 1 && !entry.validTo) periodErrors.validTo = 'End date is required.'; // i18n
      if (entry.validFrom && entry.validTo && entry.validFrom >= entry.validTo) periodErrors.validTo = 'End date must be after start date.'; // i18n
      if (index < entries.value.length - 1 && entry.validTo !== entries.value[index + 1].validFrom)
        periodErrors.validTo = 'The end date must match the next period start date.'; // i18n
      preset?.document.inputs?.forEach((input) => {
        const value = Object.hasOwn(entry.values, input.id) ? entry.values[input.id] : presetDefault(preset, input);
        if (input.required && (value === null || value === undefined || value === ''))
          periodErrors[input.id] = 'This field is required.'; // i18n
        else if (value !== null && value !== undefined && value !== '') {
          if (input.type === 'DECIMAL' && !isDecimal(value)) periodErrors[input.id] = 'Invalid decimal value.'; // i18n
          if (input.type === 'INTEGER' && (!isInteger(value) || (input.minimum !== undefined && Number(value) < input.minimum)))
            periodErrors[input.id] = 'Invalid integer value.'; // i18n
          if (input.type === 'DATETIME' && !fromDatetimeLocal(value, preset.document.timezone) && !/Z|[+-]\d\d:\d\d$/.test(value))
            periodErrors[input.id] = 'Invalid date and time.'; // i18n
          if (input.type === 'TIME' && !isTime(value)) periodErrors[input.id] = 'Invalid time.'; // i18n
        }
      });
    });
    errors.value = result;
    return !result.name && result.periods.every((periodErrors) => Object.keys(periodErrors).length === 0);
  }

  function payload() {
    const entries = configuration.value.entries.map((entry) => {
      const values = {...entry.values};
      presetDetailsById.value[entry.presetId]?.document.inputs.forEach((input) => {
        if (input.type === 'DECIMAL' && Object.hasOwn(values, input.id)) values[input.id] = normalizeDecimal(values[input.id]);
        if (input.type === 'INTEGER' && Object.hasOwn(values, input.id) && isInteger(values[input.id])) values[input.id] = Number(values[input.id]);
      });
      return {...entry, values};
    });
    return {name: name.value.trim(), configuration: serializeConfiguration({...configuration.value, entries})};
  }

  function updateEntry(index, entry) {
    configuration.value.entries[index] = entry;
  }

  function updateBoundary(index, boundary, date, timezone) {
    const value = boundary === 'validTo' ? dateToPeriodEnd(date, timezone) : dateToDatetime(date, timezone);
    configuration.value.entries[index][boundary] = value;
    normalizePeriods(index, timezone);
  }

  function normalizePeriods(index, timezone) {
    for (let previousIndex = index - 1; previousIndex >= 0; previousIndex--) {
      const previous = entries.value[previousIndex];
      const next = entries.value[previousIndex + 1];
      previous.validTo = next.validFrom;
      if (previousIndex > 0 && previous.validFrom >= previous.validTo) previous.validFrom = shiftDatetimeDays(previous.validTo, timezone, -1);
    }
    for (let nextIndex = index + 1; nextIndex < entries.value.length; nextIndex++) {
      const previous = entries.value[nextIndex - 1];
      const next = entries.value[nextIndex];
      next.validFrom = previous.validTo;
      if (nextIndex < entries.value.length - 1 && next.validTo <= next.validFrom) next.validTo = shiftDatetimeDays(next.validFrom, timezone, 1);
    }
  }

  function periodTariffLabel(entry) {
    const preset = presets.value.find((item) => item.id === entry.presetId);
    return preset ? `${preset.operator.label}: ${preset.label}` : entry.presetId;
  }

  const periodTimezone = (entry) => presetDetailsById.value[entry.presetId]?.document.timezone;

  const periodHasErrors = (index) => Object.keys(errors.value.periods[index] || {}).length > 0;

  function addPeriod() {
    const lastIndex = entries.value.length - 1;
    const lastEntry = entries.value[lastIndex];
    const timezone = presetDetailsById.value[lastEntry.presetId]?.document.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone;
    const now = new Date();
    const today = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    const boundary = dateToDatetime(today, timezone);
    lastEntry.validTo = boundary;
    configuration.value.entries.push({validFrom: boundary, presetId: '', values: {}});
    openedPeriod.value = `period-${lastIndex + 1}`;
  }

  function removePeriod(index) {
    const lastIndex = entries.value.length - 1;
    if (index === 0) delete configuration.value.entries[1].validFrom;
    else if (index === lastIndex) delete configuration.value.entries[index - 1].validTo;
    else configuration.value.entries[index - 1].validTo = configuration.value.entries[index + 1].validFrom;
    configuration.value.entries.splice(index, 1);
    if (entries.value.length > 1) normalizePeriods(Math.max(0, index - 1), periodTimezone(entries.value[Math.max(0, index - 1)]));
    openedPeriod.value = `period-${Math.max(0, index - 1)}`;
  }

  async function save(dialog) {
    if (deleteConfirmation.value) {
      await deletePlan(dialog);
      return;
    }
    if (!validate()) {
      dialog?.setLoading?.(false);
      return;
    }
    saving.value = true;
    serverError.value = null;
    try {
      const plan = createdPlan.value || (props.plan ? await store.updatePlan(props.plan.id, payload()) : await store.createPlan(payload()));
      createdPlan.value = plan;
      if (!props.plan && props.channelId) await store.assignPlan(props.channelId, plan.id);
      emit('saved', plan);
      opened.value = false;
    } catch (error) {
      serverError.value = createdPlan.value
        ? 'The cost plan was created, but could not be assigned.' /* i18n */
        : error.body?.message || 'Could not save the cost plan.'; // i18n
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
      serverError.value = error.body?.message || 'Could not delete the cost plan.'; // i18n
    } finally {
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
        <template v-if="deleteConfirmation">
          <div class="alert alert-danger">
            {{ $t('Deleting this cost plan is irreversible and unassigns it from every electricity meter that uses it.') }}
          </div>
          <p>{{ $t('Confirm deletion of the {planName} cost plan.', {planName: plan.name}) }}</p>
        </template>
        <template v-else>
          <div v-if="plan" class="alert alert-warning">{{ $t('Changes to this cost plan affect every electricity meter that uses it.') }}</div>
          <div class="form-group" :class="{'has-error': errors.name}">
            <label for="energy-cost-plan-name">{{ $t('Name') }}</label>
            <input id="energy-cost-plan-name" v-model="name" class="form-control" maxlength="255" />
            <span v-if="errors.name" class="help-block">{{ $t(errors.name) }}</span>
          </div>
          <accordion-root v-model="openedPeriod">
            <accordion-item v-for="(entry, index) in entries" :key="`${entry.presetId}-${index}`" :name="`period-${index}`" :error="periodHasErrors(index)">
              <template #title>
                {{ periodTariffLabel(entry) || $t('Choose tariff') }}
                <template v-if="entries.length > 1">
                  ({{ entry.validFrom ? dateFromDatetime(entry.validFrom, periodTimezone(entry)) : '∞' }} -
                  {{ entry.validTo ? dateFromPeriodEnd(entry.validTo, periodTimezone(entry)) : '∞' }})
                </template>
              </template>
              <energy-cost-plan-period
                :entry="entry"
                :index="index"
                :count="entries.length"
                :errors="errors.periods[index] || {}"
                :new-plan="!plan"
                @update:entry="updateEntry(index, $event)"
                @update:boundary="(boundary, date, timezone) => updateBoundary(index, boundary, date, timezone)"
                @remove="removePeriod(index)"
              />
            </accordion-item>
          </accordion-root>
          <button v-if="entries[0]?.presetId" type="button" class="btn btn-default" @click="addPeriod">{{ $t('Add period') }}</button>
          <div v-if="serverError" class="text-danger">{{ $t(serverError) }}</div>
        </template>
      </template>
      <template #footer>
        <template v-if="deleteConfirmation">
          <form-button :loading="deleting" button-class="btn-danger" @click="deletePlan">{{ $t('Delete cost plan') }}</form-button>
          <button type="button" class="btn btn-default" :disabled="deleting" @click="deleteConfirmation = false">{{ $t('Cancel') }}</button>
        </template>
        <template v-else>
          <button v-if="plan" type="button" class="btn btn-danger pull-left" :disabled="saving" @click="deleteConfirmation = true">{{ $t('Delete') }}</button>
          <form-button :loading="saving" button-class="btn-green" @click="save">{{ $t('Save changes') }}</form-button>
          <button type="button" class="btn btn-default" :disabled="saving" @click="opened = false">{{ $t('Cancel') }}</button>
        </template>
      </template>
    </dialog-content>
  </dialog-window>
</template>

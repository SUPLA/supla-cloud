<script setup>
  import {computed} from 'vue';
  import {storeToRefs} from 'pinia';
  import EnergyCostPresetPicker from './energy-cost-preset-picker.vue';
  import EnergyCostPresetForm from './energy-cost-preset-form.vue';
  import {dateFromDatetime, dateFromPeriodEnd, simulationDefaultValues} from './energy-cost-plan-utils';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';

  const props = defineProps({entry: {type: Object, required: true}, index: Number, count: Number, errors: {type: Object, required: true}, newPlan: Boolean});
  const emit = defineEmits(['update:entry', 'update:boundary', 'remove']);
  const store = useEnergyCostStore();
  const {presets, presetDetailsById} = storeToRefs(store);
  const preset = computed(() => presetDetailsById.value[props.entry.presetId]);
  const timezone = computed(() => preset.value?.document.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone);
  const isFirst = computed(() => props.index === 0);
  const isLast = computed(() => props.index === props.count - 1);

  async function updatePreset(presetId) {
    const selectedPreset = presetId ? await store.fetchPreset(presetId) : null;
    emit('update:entry', {...props.entry, presetId, values: props.newPlan && selectedPreset ? simulationDefaultValues(selectedPreset) : {}});
  }

  const updateValues = (values) => emit('update:entry', {...props.entry, values});
</script>

<template>
  <div class="energy-cost-plan-period">
    <div class="row">
      <div v-if="!isFirst" class="col-sm-6 form-group" :class="{'has-error': errors.validFrom}">
        <label :for="`energy-cost-period-${index}-from`">{{ $t('From date') }}</label>
        <input
          :id="`energy-cost-period-${index}-from`"
          type="date"
          class="form-control"
          :value="dateFromDatetime(entry.validFrom, timezone)"
          @input="$emit('update:boundary', 'validFrom', $event.target.value, timezone)"
        />
        <span v-if="errors.validFrom" class="help-block">{{ $t(errors.validFrom) }}</span>
      </div>
      <div v-if="!isLast" class="col-sm-6 form-group" :class="{'has-error': errors.validTo}">
        <label :for="`energy-cost-period-${index}-to`">{{ $t('To date') }}</label>
        <input
          :id="`energy-cost-period-${index}-to`"
          type="date"
          class="form-control"
          :value="dateFromPeriodEnd(entry.validTo, timezone)"
          @input="$emit('update:boundary', 'validTo', $event.target.value, timezone)"
        />
        <span v-if="errors.validTo" class="help-block">{{ $t(errors.validTo) }}</span>
      </div>
    </div>
    <energy-cost-preset-picker :model-value="entry.presetId" :presets="presets" :id-prefix="`energy-cost-period-${index}`" @update:model-value="updatePreset" />
    <div v-if="errors.preset" class="text-danger">{{ $t(errors.preset) }}</div>
    <energy-cost-preset-form
      v-if="preset"
      :entry="entry"
      :preset="preset"
      :errors="errors"
      :id-prefix="`energy-cost-period-${index}`"
      @update:values="updateValues"
    />
    <button v-if="count > 1" type="button" class="btn btn-link text-danger" @click="$emit('remove')">{{ $t('Remove period') }}</button>
  </div>
</template>

<style scoped>
  .energy-cost-plan-period > .row {
    margin-right: 0;
    margin-left: 0;
  }
</style>

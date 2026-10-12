<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed} from 'vue';
  import {fromDatetimeLocal, normalizeDecimal, toDatetimeLocal} from './energy-cost-plan-utils';

  const props = defineProps({
    input: {type: Object, required: true},
    idPrefix: {type: String, default: 'energy-cost'},
    value: [String, Number],
    timezone: String,
    custom: Boolean,
    hasDefault: Boolean,
    error: String,
  });
  const emit = defineEmits(['update:value', 'override', 'reset']);

  const displayedValue = computed(() => (props.input.type === 'DATETIME' ? toDatetimeLocal(props.value, props.timezone) : (props.value ?? '')));
  const inputType = computed(() => {
    if (props.input.type === 'INTEGER') return 'number';
    if (props.input.type === 'DATE') return 'date';
    if (props.input.type === 'DATETIME') return 'datetime-local';
    if (props.input.type === 'TIME') return 'time';
    return 'text';
  });
  const displayedLabel = computed(() => props.input.options?.find((option) => option.value === props.value)?.label || props.value);

  function updateValue(value) {
    if (props.input.type === 'DECIMAL') value = normalizeDecimal(value);
    if (props.input.type === 'DATETIME') value = fromDatetimeLocal(value, props.timezone);
    emit('update:value', value);
  }
</script>

<template>
  <div class="form-group energy-cost-preset-input" :class="{'has-error': error}">
    <label :for="`${idPrefix}-input-${input.id}`">{{ input.label }}</label>
    <div class="preset-input-controls">
      <template v-if="custom">
        <select
          v-if="input.type === 'CHOICE'"
          :id="`${idPrefix}-input-${input.id}`"
          :value="displayedValue"
          class="form-control"
          @change="updateValue($event.target.value)"
        >
          <option v-for="option in input.options || []" :key="option.value" :value="option.value">{{ option.label }}</option>
        </select>
        <input
          v-else
          :id="`${idPrefix}-input-${input.id}`"
          :type="inputType"
          :value="displayedValue"
          :inputmode="input.type === 'DECIMAL' ? 'decimal' : undefined"
          :min="input.type === 'INTEGER' ? input.minimum : undefined"
          :step="input.type === 'INTEGER' ? 1 : undefined"
          class="form-control"
          @input="updateValue($event.target.value)"
        />
        <small v-if="input.unit" class="form-text text-muted">{{ input.unit }}</small>
        <button v-if="hasDefault" type="button" class="btn btn-link btn-xs" @click="$emit('reset')">
          {{ $t('Use tariff value') }}
        </button>
      </template>
      <template v-else>
        <div class="form-control-static" :title="$t('Value from tariff')">
          {{ input.type === 'CHOICE' ? displayedLabel : value }} <small v-if="input.unit">{{ input.unit }}</small>
        </div>
        <button type="button" class="btn btn-link btn-xs" @click="$emit('override')">{{ $t('Override') }}</button>
      </template>
    </div>
    <small v-if="input.help" class="preset-input-help text-muted">{{ input.help }}</small>
    <span v-if="error" class="help-block">{{ $t(error) }}</span>
  </div>
</template>

<style scoped>
  .energy-cost-preset-input {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 5px 12px;
    margin-bottom: 8px;
  }
  .energy-cost-preset-input > label {
    flex: 1 1 180px;
    margin-bottom: 0;
  }
  .preset-input-controls {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 5px 8px;
  }
  .preset-input-controls .form-control {
    width: 120px;
    height: 30px;
    padding: 4px 8px;
  }
  .preset-input-controls select,
  .preset-input-controls input[type='datetime-local'] {
    width: 200px;
  }
  .preset-input-controls .form-control-static {
    min-height: 0;
    padding: 0;
  }
  .preset-input-controls .btn,
  .preset-input-controls .form-text {
    white-space: nowrap;
  }
  .preset-input-help,
  .help-block {
    flex-basis: 100%;
    margin: 0;
  }
</style>

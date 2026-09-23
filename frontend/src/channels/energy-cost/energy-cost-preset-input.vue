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
  const inputType = computed(() => (props.input.type === 'INTEGER' ? 'number' : props.input.type === 'DATETIME' ? 'datetime-local' : 'text'));

  function updateValue(value) {
    if (props.input.type === 'DECIMAL') value = normalizeDecimal(value);
    if (props.input.type === 'DATETIME') value = fromDatetimeLocal(value, props.timezone);
    emit('update:value', value);
  }
</script>

<template>
  <div class="form-group energy-cost-preset-input" :class="{'has-error': error}">
    <label :for="`${idPrefix}-input-${input.id}`">{{ input.label }}</label>
    <template v-if="custom">
      <input
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
      <div class="form-control-static">
        {{ value }} <small v-if="input.unit">{{ input.unit }}</small>
      </div>
      <small class="form-text text-muted">{{ $t('Value from tariff') }}</small>
      <button type="button" class="btn btn-link btn-xs" @click="$emit('override')">{{ $t('Override') }}</button>
    </template>
    <small v-if="input.help" class="form-text text-muted">{{ input.help }}</small>
    <span v-if="error" class="help-block">{{ $t(error) }}</span>
  </div>
</template>

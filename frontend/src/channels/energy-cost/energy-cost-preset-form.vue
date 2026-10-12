<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed} from 'vue';
  import {useI18n} from 'vue-i18n';
  import {DateTime} from 'luxon';
  import EnergyCostPresetInput from './energy-cost-preset-input.vue';
  import {componentInputsOutsidePricePeriods, pricePeriodsForComponent, presetDefault} from './energy-cost-plan-utils';

  const props = defineProps({
    component: {type: Object, required: true},
    preset: {type: Object, required: true},
    errors: {type: Object, required: true},
    idPrefix: {type: String, default: 'energy-cost'},
  });
  const emit = defineEmits(['update:values']);
  const {locale} = useI18n();
  const formatDate = (value, end = false) => {
    let date = DateTime.fromISO(value).setZone(props.preset.document.timezone).setLocale(locale.value);
    if (end && date.equals(date.startOf('day'))) date = date.minus({days: 1});
    return date.toLocaleString({year: 'numeric', month: '2-digit', day: '2-digit'});
  };

  const pricePeriods = computed(() => pricePeriodsForComponent(props.preset, props.component.componentId));
  const componentInputs = computed(() => componentInputsOutsidePricePeriods(props.preset, props.component.componentId));
  const effectiveValue = (input) =>
    Object.hasOwn(props.component.values, input.id) ? props.component.values[input.id] : presetDefault(props.preset, input, props.component.componentId);
  const hasDefault = (input) =>
    presetDefault(props.preset, input, props.component.componentId) !== null && presetDefault(props.preset, input, props.component.componentId) !== undefined;
  const isCustom = (input) => Object.hasOwn(props.component.values, input.id) || !hasDefault(input);
  const override = (input) => emit('update:values', {...props.component.values, [input.id]: effectiveValue(input) ?? ''});
  const reset = (input) => {
    const values = {...props.component.values};
    delete values[input.id];
    emit('update:values', values);
  };
  const update = (input, value) => emit('update:values', {...props.component.values, [input.id]: value});
</script>

<template>
  <div class="energy-cost-preset-form">
    <div v-if="preset.document.warnings?.length" class="alert alert-warning">
      <div v-for="warning in preset.document.warnings" :key="warning">{{ warning }}</div>
    </div>
    <div v-for="{period, pricePeriodIndex, inputs} in pricePeriods" :key="pricePeriodIndex" class="energy-cost-price-period">
      <div v-if="period.validFrom || period.validTo" class="price-period-heading">
        <template v-if="period.validFrom && period.validTo">{{ formatDate(period.validFrom) }} - {{ formatDate(period.validTo, true) }}</template>
        <template v-else-if="period.validTo">{{ $t('until {date}', {date: formatDate(period.validTo, true)}) }}</template>
        <template v-else>{{ $t('from {date}', {date: formatDate(period.validFrom)}) }}</template>
      </div>
      <energy-cost-preset-input
        v-for="input in inputs"
        :key="input.id"
        :input="input"
        :id-prefix="`${idPrefix}-${pricePeriodIndex}`"
        :value="effectiveValue(input)"
        :timezone="preset.document.timezone"
        :custom="isCustom(input)"
        :has-default="hasDefault(input)"
        :error="errors[input.id]"
        @override="override(input)"
        @reset="reset(input)"
        @update:value="update(input, $event)"
      />
    </div>
    <div v-if="componentInputs.length" class="energy-cost-component-inputs">
      <energy-cost-preset-input
        v-for="input in componentInputs"
        :key="input.id"
        :input="input"
        :id-prefix="idPrefix"
        :value="effectiveValue(input)"
        :timezone="preset.document.timezone"
        :custom="isCustom(input)"
        :has-default="hasDefault(input)"
        :error="errors[input.id]"
        @override="override(input)"
        @reset="reset(input)"
        @update:value="update(input, $event)"
      />
    </div>
  </div>
</template>

<style scoped>
  .energy-cost-price-period + .energy-cost-price-period {
    padding-top: 8px;
    border-top: 1px solid #ddd;
  }
  .price-period-heading {
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: bold;
  }
</style>

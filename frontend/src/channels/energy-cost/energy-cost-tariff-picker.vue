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
  import {tariffCatalogPresentation} from './energy-cost-plan-presentation';

  const props = defineProps({
    tariffs: {type: Array, required: true},
    modelValue: String,
    idPrefix: {type: String, default: 'energy-cost'},
    allowNoTariff: Boolean,
    hideLabel: Boolean,
  });
  defineEmits(['update:modelValue']);
  const {t} = useI18n();
  const groupedTariffs = computed(() => {
    const groups = new Map();
    props.tariffs.forEach((tariff) => {
      const label = tariffCatalogPresentation(tariff, t).group;
      groups.set(label, [...(groups.get(label) || []), tariff]);
    });
    return [...groups.entries()];
  });
</script>

<template>
  <div class="energy-cost-tariff-picker form-group">
    <label :for="`${idPrefix}-tariff`" :class="{'sr-only': hideLabel}">{{ $t('Tariff') }}</label>
    <select :id="`${idPrefix}-tariff`" :value="modelValue" class="form-control" @change="$emit('update:modelValue', $event.target.value)">
      <option value="">{{ $t('Choose tariff') }}</option>
      <optgroup v-for="[operator, operatorTariffs] in groupedTariffs" :key="operator" :label="operator">
        <option v-for="tariff in operatorTariffs" :key="tariff.id" :value="tariff.id">{{ tariffCatalogPresentation(tariff, t).label }}</option>
      </optgroup>
      <option v-if="allowNoTariff" value="__no_tariff__">{{ $t('No tariff') }}</option>
    </select>
  </div>
</template>

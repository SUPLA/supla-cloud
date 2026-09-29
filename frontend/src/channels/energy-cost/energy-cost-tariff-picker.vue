<script setup>
  import {computed} from 'vue';

  const props = defineProps({tariffs: {type: Array, required: true}, modelValue: String, idPrefix: {type: String, default: 'energy-cost'}});
  defineEmits(['update:modelValue']);
  const groupedTariffs = computed(() => {
    const groups = new Map();
    props.tariffs.forEach((tariff) => {
      const label = tariff.operator?.label || tariff.operator || '';
      groups.set(label, [...(groups.get(label) || []), tariff]);
    });
    return [...groups.entries()];
  });
</script>

<template>
  <div class="energy-cost-tariff-picker form-group">
    <label :for="`${idPrefix}-tariff`">{{ $t('Tariff') }}</label>
    <select :id="`${idPrefix}-tariff`" :value="modelValue" class="form-control" @change="$emit('update:modelValue', $event.target.value)">
      <option value="">{{ $t('Choose tariff') }}</option>
      <optgroup v-for="[operator, operatorTariffs] in groupedTariffs" :key="operator" :label="operator">
        <option v-for="tariff in operatorTariffs" :key="tariff.id" :value="tariff.id">{{ tariff.label }}</option>
      </optgroup>
    </select>
  </div>
</template>

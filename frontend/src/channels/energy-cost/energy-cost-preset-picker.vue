<script setup>
  import {computed, ref, watch} from 'vue';

  const props = defineProps({presets: {type: Array, required: true}, modelValue: String, idPrefix: {type: String, default: 'energy-cost'}});
  const emit = defineEmits(['update:modelValue']);
  const selectedOperatorId = ref('');

  const operators = computed(() => {
    const values = new Map();
    props.presets.forEach((preset) => values.set(preset.operator.id, preset.operator));
    return [...values.values()];
  });
  const tariffs = computed(() => props.presets.filter((preset) => preset.operator.id === selectedOperatorId.value));

  watch(
    () => [props.modelValue, props.presets],
    () => {
      const preset = props.presets.find((item) => item.id === props.modelValue);
      if (preset) selectedOperatorId.value = preset.operator.id;
    },
    {immediate: true}
  );

  function selectOperator() {
    if (props.presets.find((preset) => preset.id === props.modelValue)?.operator.id !== selectedOperatorId.value) emit('update:modelValue', '');
  }
</script>

<template>
  <div class="energy-cost-preset-picker row">
    <div class="form-group col-sm-6">
      <label :for="`${idPrefix}-operator`">{{ $t('Distribution operator') }}</label>
      <select :id="`${idPrefix}-operator`" v-model="selectedOperatorId" class="form-control" @change="selectOperator">
        <option value="">{{ $t('Choose distribution operator') }}</option>
        <option v-for="operator in operators" :key="operator.id" :value="operator.id">{{ operator.label }}</option>
      </select>
    </div>
    <div v-if="selectedOperatorId" class="form-group col-sm-6">
      <label :for="`${idPrefix}-preset`">{{ $t('Tariff') }}</label>
      <select :id="`${idPrefix}-preset`" :value="modelValue" class="form-control" @change="$emit('update:modelValue', $event.target.value)">
        <option value="">{{ $t('Choose tariff') }}</option>
        <option v-for="preset in tariffs" :key="preset.id" :value="preset.id">{{ preset.label }}</option>
      </select>
    </div>
  </div>
</template>

<style scoped>
  .energy-cost-preset-picker {
    margin-right: 0;
    margin-left: 0;
  }

  .energy-cost-preset-picker > :first-child {
    padding-left: 0;
  }

  .energy-cost-preset-picker > :last-child {
    padding-right: 0;
  }
</style>

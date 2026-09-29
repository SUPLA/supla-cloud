<script setup>
  defineProps({presets: {type: Array, required: true}, modelValue: String, idPrefix: {type: String, default: 'energy-cost'}});
  defineEmits(['update:modelValue']);
</script>

<template>
  <div class="energy-cost-preset-picker form-group">
    <label :for="`${idPrefix}-preset`">{{ $t('Tariff') }}</label>
    <select :id="`${idPrefix}-preset`" :value="modelValue" class="form-control" @change="$emit('update:modelValue', $event.target.value)">
      <option value="">{{ $t('Choose tariff') }}</option>
      <option v-for="preset in presets" :key="preset.id" :value="preset.id">{{ preset.label }}</option>
    </select>
  </div>
</template>

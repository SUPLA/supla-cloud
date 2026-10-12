<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {useI18n} from 'vue-i18n';
  import {tariffCatalogPresentation} from './energy-cost-plan-presentation';
  const {t} = useI18n();
  defineProps({presets: {type: Array, required: true}, modelValue: String, idPrefix: {type: String, default: 'energy-cost'}});
  defineEmits(['update:modelValue']);
</script>

<template>
  <div class="energy-cost-preset-picker form-group">
    <label :for="`${idPrefix}-preset`">{{ $t('Pricing source') }}</label>
    <select :id="`${idPrefix}-preset`" :value="modelValue" class="form-control" @change="$emit('update:modelValue', $event.target.value)">
      <option value="">{{ $t('Choose pricing source') }}</option>
      <option v-for="preset in presets" :key="preset.id" :value="preset.id">{{ tariffCatalogPresentation(preset, t).label }}</option>
    </select>
  </div>
</template>

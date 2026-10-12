<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {ref, watch} from 'vue';
  import {useI18n} from 'vue-i18n';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {tariffCatalogPresentation} from './energy-cost-plan-presentation';
  const {t} = useI18n();
  const props = defineProps({presets: {type: Array, required: true}, modelValue: String, source: Object, idPrefix: {type: String, default: 'energy-cost'}});
  defineEmits(['update:modelValue']);
  const detailsVisible = ref(false);
  watch(
    () => props.modelValue,
    () => (detailsVisible.value = false)
  );
</script>

<template>
  <div class="energy-cost-preset-picker form-group">
    <label :for="`${idPrefix}-preset`">{{ $t('Pricing source') }}</label>
    <button
      v-if="source"
      type="button"
      class="btn btn-link btn-xs tariff-details-toggle"
      :aria-label="$t('Tariff details')"
      :title="$t('Tariff details')"
      :aria-expanded="detailsVisible"
      :aria-controls="`${idPrefix}-source-details`"
      @click="detailsVisible = !detailsVisible"
    >
      <span aria-hidden="true">ⓘ</span>
    </button>
    <select :id="`${idPrefix}-preset`" :value="modelValue" class="form-control" @change="$emit('update:modelValue', $event.target.value)">
      <option value="">{{ $t('Choose pricing source') }}</option>
      <option v-for="preset in presets" :key="preset.id" :value="preset.id">{{ tariffCatalogPresentation(preset, t).label }}</option>
    </select>
    <transition-expand>
      <div v-if="source && detailsVisible" :id="`${idPrefix}-source-details`" class="alert alert-info tariff-source-details">
        <p v-if="source.title">{{ source.title }}</p>
        <p v-if="source.retrievedAt">{{ $t('Retrieved') }}: {{ source.retrievedAt }}</p>
        <a v-if="source.url" :href="source.url" target="_blank" rel="noopener noreferrer">{{ source.url }}</a>
      </div>
    </transition-expand>
  </div>
</template>

<style scoped>
  .tariff-details-toggle {
    margin-left: 4px;
    font-size: 16px;
    line-height: 1;
  }
  .tariff-source-details {
    margin: 8px 0 0;
    overflow-wrap: anywhere;
  }
</style>

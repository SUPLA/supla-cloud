<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import EnergyCostHeatmap from './energy-cost-heatmap.vue';

  defineProps({buckets: Array, energyBuckets: Array, currency: String, modelValue: {type: String, default: 'cost'}});
  const emit = defineEmits(['update:modelValue', 'rendering']);
</script>

<template>
  <section class="energy-cost-print-chart energy-cost-interactive-heatmap">
    <div class="clearfix">
      <h3 class="pull-left">{{ $t(modelValue === 'usage' ? 'Energy usage by weekday and hour' : 'Gross cost by weekday and hour') }}</h3>
      <div class="btn-group pull-right">
        <button type="button" class="btn btn-default" :class="{active: modelValue === 'cost'}" @click="emit('update:modelValue', 'cost')">
          {{ $t('Total cost') }}
        </button>
        <button type="button" class="btn btn-default" :class="{active: modelValue === 'costPerKwh'}" @click="emit('update:modelValue', 'costPerKwh')">
          {{ $t('Cost per kWh') }}
        </button>
        <button type="button" class="btn btn-default" :class="{active: modelValue === 'usage'}" @click="emit('update:modelValue', 'usage')">
          {{ $t('Energy usage') }}
        </button>
      </div>
    </div>
    <p class="text-muted">
      {{
        modelValue === 'cost'
          ? $t('When did I spend the most?')
          : modelValue === 'usage'
            ? $t('When did I use the most energy?')
            : $t('When is electricity intrinsically most expensive?')
      }}
    </p>
    <energy-cost-heatmap :buckets="buckets" :energy-buckets="energyBuckets" :currency="currency" :metric="modelValue" @rendering="emit('rendering', $event)" />
  </section>
</template>

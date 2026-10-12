<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  defineProps({meters: Array, modelValue: String});
  defineEmits(['update:modelValue']);
</script>
<template>
  <div class="alert alert-info">
    <p>{{ $t('This tariff plan is used by the following electricity meters:') }}</p>
    <ul>
      <li v-for="meter in meters" :key="meter.id">{{ meter.caption || $t('Electricity meter') }} (#{{ meter.id }})</li>
    </ul>
    <label><input type="radio" :checked="modelValue === 'thisMeter'" @change="$emit('update:modelValue', 'thisMeter')" /> {{ $t('Only this meter') }}</label>
    <br />
    <label
      ><input type="radio" :checked="modelValue === 'allMeters'" @change="$emit('update:modelValue', 'allMeters')" />
      {{ $t('All meters using this plan') }}</label
    >
  </div>
</template>

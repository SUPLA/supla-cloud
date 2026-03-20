<template>
  <div class="reaction-condition-duration">
    <div class="d-flex">
      <label class="flex-grow-1">{{ $t('Condition must be true for minimum time') }}</label>
      <div v-tooltip="$t('The reaction will fire only after the condition is met for the specified duration.')">
        <i class="pe-7s-help1"></i>
      </div>
    </div>
    <div class="px-4">
      <TimeIntervalSlider v-model="model" :min="minDuration" :max="3600" seconds class="mt-5" no-fractions />
    </div>
  </div>
</template>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import TimeIntervalSlider from '@/scenes/time-interval-slider.vue';

  defineProps({
    minDuration: {type: Number, default: 0},
    defaultDuration: {type: Number, default: 10},
  });

  const model = defineModel({type: Number});
</script>

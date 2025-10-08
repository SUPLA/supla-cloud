<template>
  <TimeIntervalSlider v-model="model" :max="3600000">
    <template #buttons>
      <a @click="$emit('delete')">
        <fa :icon="faTrash" />
      </a>
    </template>
  </TimeIntervalSlider>
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
  import {faTrash} from '@fortawesome/free-solid-svg-icons';

  const model = defineModel();
</script>

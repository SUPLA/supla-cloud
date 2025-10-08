<script setup>
  import VueSlider from "vue-slider-component";
  import 'vue-slider-component/theme/antd.css';

  defineProps({
    min: {type: Number, default: 0},
    max: {type: Number, default: 100},
    interval: {type: Number, default: 1},
    disabled: {type: Boolean, default: false},
    tooltipFormatter: {type: Function, default: (v) => v},
  });

  const model = defineModel('model', {type: Number, default: 0});
</script>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    }
  }
</script>

<template>
  <VueSlider v-model="model" :min="min" :max="max" :interval="interval" :disabled="disabled"
    tooltip="always" tooltip-placement="bottom"
    :tooltip-formatter="tooltipFormatter" class="green">
    <template #label="{label}">
      <slot name="label" :label="label"/>
    </template>
  </VueSlider>
</template>

<style lang="scss">
  @use "../../styles/variables" as *;
  @use "sass:color";

  .vue-slider-mark-label.mark-on-top {
    top: auto !important;
    bottom: 100%;
    margin-bottom: 10px;
  }

  .vue-slider.green {
    .vue-slider-process {
      background-color: $supla-green;
    }
    .vue-slider-dot-handle {
      border-color: $supla-green;
    }
    &:hover {
      .vue-slider-process {
        background-color: color.adjust($supla-green, $lightness: -10%);
      }
      .vue-slider-dot-handle {
        border-color: color.adjust($supla-green, $lightness: -10%);
        &:hover {
          border-color: $supla-green;
        }
      }
    }
  }

</style>

<template>
  <div class="d-flex justify-content-center flex-column align-items-flex-end">
    <div ref="picker"></div>
    <div class="mt-2">
      <input v-model.number="model" type="number" min="0" max="100" step="1" class="form-control" />
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
  import iro from '@jaames/iro';
  import {onMounted, useTemplateRef, watch} from 'vue';

  const pickerElement = useTemplateRef('picker');

  let colorPicker;

  const model = defineModel({type: Number});

  const MIN_K = 2200;
  const MAX_K = 11000;

  const kelvinToPercent = (kelvin) => Math.round(((kelvin - MIN_K) / (MAX_K - MIN_K)) * 100);
  const percentToKelvin = (percent) => Math.round((percent / 100) * (MAX_K - MIN_K) + MIN_K);

  onMounted(() => {
    colorPicker = new iro.ColorPicker(pickerElement.value, {
      layout: [
        {
          component: iro.ui.Slider,
          options: {sliderType: 'kelvin', minTemperature: MIN_K, maxTemperature: MAX_K},
        },
      ],
    });
    colorPicker.on('mount', () => (colorPicker.color.kelvin = percentToKelvin(model.value)));
    colorPicker.on('color:change', function (color) {
      const value = kelvinToPercent(color.kelvin);
      if (value !== model.value) {
        model.value = value;
      }
    });
  });

  watch(
    () => model.value,
    (v) => {
      if (!colorPicker || v == null) return;
      const pickerV = kelvinToPercent(colorPicker.color.kelvin);
      if (pickerV !== v) {
        colorPicker.color.kelvin = percentToKelvin(v);
      }
    }
  );
</script>

<style scoped>
  input[type='number'] {
    max-width: 70px;
  }
</style>

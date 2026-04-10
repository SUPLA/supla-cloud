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
  let updatingFromModel = false;
  let updatingFromPicker = false;

  const model = defineModel({type: Number});

  const MIN_K = 2200;
  const MAX_K = 11000;

  const clampPercent = (v) => Math.min(100, Math.max(0, Math.round(v)));
  const kelvinToPercent = (kelvin) => clampPercent(((kelvin - MIN_K) / (MAX_K - MIN_K)) * 100);

  const percentToKelvin = (percent) => Math.round((clampPercent(percent) / 100) * (MAX_K - MIN_K) + MIN_K);

  onMounted(() => {
    colorPicker = new iro.ColorPicker(pickerElement.value, {
      layout: [
        {
          component: iro.ui.Slider,
          options: {
            sliderType: 'kelvin',
            minTemperature: MIN_K,
            maxTemperature: MAX_K,
          },
        },
      ],
    });

    colorPicker.on('mount', () => {
      updatingFromModel = true;
      colorPicker.color.kelvin = percentToKelvin(model.value ?? 0);
      updatingFromModel = false;
    });

    colorPicker.on('color:change', (color) => {
      if (updatingFromModel) return;

      const value = kelvinToPercent(color.kelvin);

      if (value !== model.value) {
        updatingFromPicker = true;
        model.value = value;
        updatingFromPicker = false;
      }
    });
  });

  watch(
    () => model.value,
    (v) => {
      if (!colorPicker || v == null || updatingFromPicker) return;

      if (v !== undefined) {
        const clamped = Math.max(0, Math.min(100, v));
        if (clamped !== v) {
          model.value = clamped;
          return;
        }
      }

      const normalized = clampPercent(v);

      if (normalized !== v) {
        model.value = normalized;
        return;
      }

      updatingFromModel = true;
      colorPicker.color.kelvin = percentToKelvin(normalized);
      updatingFromModel = false;
    }
  );
</script>

<style scoped>
  input[type='number'] {
    max-width: 70px;
  }
</style>

<template>
  <div class="d-flex justify-content-center">
    <div ref="picker"></div>
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

  const kelvinToPercent = (kelvin) => Math.round(((kelvin - 2200) * 100) / 8800);
  const percentToKelvin = (percent) => (percent * 8800) / 100 + 2200;

  onMounted(() => {
    colorPicker = new iro.ColorPicker(pickerElement.value, {
      layout: [
        {
          component: iro.ui.Slider,
          options: {sliderType: 'kelvin', minTemperature: 2200, maxTemperature: 11000},
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

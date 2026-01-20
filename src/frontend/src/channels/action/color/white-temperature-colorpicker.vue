<template>
  <div ref="picker"></div>
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

  function updateColorFromModel() {
    colorPicker.color.kelvin = model.value;
  }

  onMounted(() => {
    colorPicker = new iro.ColorPicker(pickerElement.value, {
      layout: [
        {
          component: iro.ui.Slider,
          options: {sliderType: 'kelvin', minTemperature: 2200, maxTemperature: 11000},
        },
      ],
    });
    colorPicker.on('mount', updateColorFromModel);
    colorPicker.on('color:change', function (color) {
      model.value = Math.round(((color.kelvin - 2200) * 100) / 8800);
    });
  });

  // watch(() => model.value, updateColorFromModel);
</script>

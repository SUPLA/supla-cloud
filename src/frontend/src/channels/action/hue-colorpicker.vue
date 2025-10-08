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

  onMounted(() => {
    colorPicker = new iro.ColorPicker(pickerElement.value, {
      layout: [
        {
          component: iro.ui.Slider,
          options: {sliderType: 'hue'},
        },
      ],
      color: {h: model.value, s: 100, v: 100},
    });
    colorPicker.on('color:change', function (color) {
      const value = Math.round(color.hue);
      if (value !== model.value) {
        model.value = value > 360 ? 0 : value;
      }
    });
  });

  watch(
    () => model.value,
    (h) => (colorPicker.color.hsv = {h, s: 100, v: 100})
  );
</script>

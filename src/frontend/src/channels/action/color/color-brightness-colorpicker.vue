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

  const props = defineProps({color: String});

  const model = defineModel({type: Number});

  function updateColorFromModel() {
    colorPicker.color.set(props.color || '#FFFFFF');
    colorPicker.color.hsv = {...colorPicker.color.hsv, v: Math.round(model.value)};
  }

  onMounted(() => {
    colorPicker = new iro.ColorPicker(pickerElement.value, {
      layout: [
        {
          component: iro.ui.Slider,
          options: {sliderType: 'value'},
        },
      ],
    });
    colorPicker.on('mount', updateColorFromModel);
    colorPicker.on('color:change', function (color) {
      model.value = Math.max(Math.round(color.hsv.v), 1);
    });
  });

  watch(() => model.value, updateColorFromModel);
  watch(() => props.color, updateColorFromModel);
</script>

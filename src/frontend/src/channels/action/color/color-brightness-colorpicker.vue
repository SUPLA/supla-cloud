<template>
  <div class="d-flex justify-content-center flex-column align-items-flex-end">
    <div ref="picker"></div>
    <div class="d-flex align-items-center flex-gap-1 mt-2">
      <input v-model.number="model" type="number" min="0" max="100" step="1" class="form-control" />
      <div>%</div>
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

  const props = defineProps({color: String});

  const model = defineModel({type: Number});

  onMounted(() => {
    colorPicker = new iro.ColorPicker(pickerElement.value, {
      layout: [
        {
          component: iro.ui.Slider,
          options: {sliderType: 'value'},
        },
      ],
    });
    colorPicker.on('mount', () => {
      colorPicker.color.set(props.color || '#FFFFFF');
      colorPicker.color.hsv = {...colorPicker.color.hsv, v: model.value};
    });
    colorPicker.on('color:change', function (color) {
      const v = Math.max(Math.round(color.hsv.v), 0);
      if (model.value !== v) {
        model.value = v;
      }
    });
  });

  watch(
    () => model.value,
    (v) => {
      if (!colorPicker || v === undefined) return;
      const pickerV = Math.round(colorPicker.color.hsv.v);
      const theV = Math.round(v);
      if (pickerV !== theV) {
        colorPicker.color.hsv = {...colorPicker.color.hsv, v: theV};
      }
    }
  );
  watch(
    () => props.color,
    (v) => {
      if (!colorPicker || v === undefined) return;
      const pickerV = colorPicker.color.hexString.toUpperCase();
      if (pickerV !== v) {
        colorPicker.color.set(v);
      }
    }
  );
</script>

<style scoped>
  input[type='number'] {
    max-width: 70px;
  }
</style>

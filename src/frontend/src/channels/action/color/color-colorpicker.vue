<template>
  <div class="d-flex align-items-center flex-column">
    <div ref="picker" class="wheel-picker mb-3" @click="stopEditHex"></div>
    <input type="text" v-model="hexFieldValue" class="form-control text-center" @focus="startEditHex" @blur="stopEditHex" maxlength="7" ref="colorInput" />
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
  import {computed, onMounted, ref, useTemplateRef, watch} from 'vue';

  const props = defineProps({brightness: Number});
  const emit = defineEmits(['manualColorChange']);

  const pickerElement = useTemplateRef('picker');
  const colorInput = useTemplateRef('colorInput');

  let colorPicker;

  const model = defineModel({type: String});

  // const color = computed(() => ({h: model.value.hue, s: 100, v: model.value.colorBrightness}));

  // const colorFromPicker = ref('');
  const hexFieldUserEdit = ref('');
  const editingHex = ref(false);
  const startEditHex = () => {
    hexFieldUserEdit.value = hexFieldValue.value;
    editingHex.value = true;
  };
  const stopEditHex = () => {
    if (editingHex.value) {
      colorInput.value.blur();
      editingHex.value = false;
      hexFieldValue.value = hexFieldUserEdit.value;
      emit('manualColorChange', colorPicker.color.hsv);
    }
  };

  const hexFieldValue = computed({
    get: () => (editingHex.value ? hexFieldUserEdit.value : model.value.toUpperCase()),
    set: (newValue) => {
      hexFieldUserEdit.value = newValue;
      try {
        colorPicker.color.set(newValue);
      } catch (e) {
        // console.info('Error setting hex value:', e);
      }
    },
  });

  function updateColorFromModel() {
    if (!editingHex.value) {
      colorPicker.color.set(model.value || '#FFFFFF');
      colorPicker.color.hsv = {...colorPicker.color.hsv, v: props.brightness !== undefined ? props.brightness : 100};
    }
  }

  onMounted(() => {
    colorPicker = new iro.ColorPicker(pickerElement.value, {
      layout: [
        {
          component: iro.ui.Wheel,
          options: {wheelLightness: false, width: 150, wheelDirection: 'clockwise'},
        },
      ],
      // color: model.value,
    });
    colorPicker.on('mount', updateColorFromModel);
    // colorPicker.on('color:init', function (color) {
    //   colorFromPicker.value = color.hexString;
    // });
    colorPicker.on('color:change', function (color) {
      setTimeout(() => (model.value = color.hexString.toUpperCase()));
    });
  });

  watch(() => model.value, updateColorFromModel);
  watch(() => props.brightness, updateColorFromModel);
</script>

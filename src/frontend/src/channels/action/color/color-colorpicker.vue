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
  const emit = defineEmits(['onNewBrightness']);

  const pickerElement = useTemplateRef('picker');
  const colorInput = useTemplateRef('colorInput');

  let colorPicker;

  const model = defineModel({type: String});

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
      emit('onNewBrightness', Math.round(colorPicker.color.hsv.v));
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
    });
    colorPicker.on('mount', updateColorFromModel);
    colorPicker.on('color:change', function (color) {
      const v = color.hexString.toUpperCase();
      if (model.value !== v) {
        model.value = v;
      }
    });
  });

  watch(
    () => model.value,
    (v) => {
      if (!colorPicker || v == null) return;
      const pickerV = colorPicker.color.hexString.toUpperCase();
      if (pickerV !== v) {
        colorPicker.color.set(v);
      }
    }
  );
  watch(
    () => props.brightness,
    (v) => {
      if (!colorPicker || v == null) return;
      const pickerV = Math.round(colorPicker.color.hsv.v);
      const theV = Math.round(v);
      if (pickerV !== theV) {
        colorPicker.color.hsv = {...colorPicker.color.hsv, v: theV};
      }
    }
  );
</script>

<template>
  <span :class="{'input-group': suffix}">
    <input
      type="number"
      class="form-control text-center"
      :class="{'no-spinner': noSpinner}"
      v-model="model"
      :placeholder="placeholder"
      :min="min"
      :max="max"
      :step="step"
      @blur="validateMinMax()"
    />
    <span v-if="suffix" class="input-group-addon">{{ suffix }}</span>
  </span>
</template>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed} from 'vue';

  const props = defineProps({
    min: {
      type: Number,
      default: 0,
    },
    max: Number,
    suffix: String,
    placeholder: String,
    noSpinner: Boolean,
    required: Boolean,
    precision: {
      type: Number,
      default: 0,
    },
  });

  const step = computed(() => Math.pow(10, -props.precision));
  const model = defineModel();

  function validateMinMax() {
    if (model.value === '') {
      model.value = props.required ? props.min || 0 : undefined;
    } else {
      if (props.min !== undefined && model.value < props.min) {
        model.value = props.min;
      }
      if (props.max !== undefined && model.value > props.max) {
        model.value = props.max;
      }
    }
  }
</script>

<style lang="scss" scoped>
  input.no-spinner::-webkit-outer-spin-button,
  input.no-spinner::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  input.no-spinner[type='number'] {
    -moz-appearance: textfield; /* Firefox */
  }
</style>

<template>
    <VueNumber
        :model-value="theValue"
        @update:model-value="theValue = $event"
        :min="min"
        :max="max"
        :suffix="suffix"
        decimal="."
        separator=" "
        :precision="precision"
        class="form-control text-center"/>
</template>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  }
</script>

<script setup>
  import {component as VueNumber} from '@coders-tm/vue-number-format'
  import {computed} from "vue";

  const props = defineProps({
    min: {
      type: Number,
      default: 0,
    },
    max: Number,
    suffix: String,
    precision: {
      type: Number,
      default: 0,
    },
  });

  const model = defineModel();

  const theValue = computed({
    get: () => model.value,
    set: (value) => model.value = Math.max(props.min, +value),
  })
</script>

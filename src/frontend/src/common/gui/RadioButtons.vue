<script setup>
  defineProps({
    options: {
      type: Array,
      required: true,
      default: () => [],
    },
  });

  const model = defineModel();
</script>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<template>
  <div class="btn-group btn-group-justified">
    <div v-for="option in options" :key="option.value" class="btn-group">
      <button type="button" class="btn" :class="model === option.value ? 'btn-green' : 'btn-default'" @click="model = option.value">
        {{ option.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
  import {computed} from 'vue';

  const props = defineProps({color: String});

  const backgroundColor = computed(() => props.color.replace('0x', '#'));

  const isColorDark = (hexColor) => {
    const hex = hexColor.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    return luminance < 0.5;
  };

  const textColor = computed(() => (isColorDark(backgroundColor.value) ? '#ffffff' : '#000000'));
</script>

<template>
  <span class="d-inline-block color-preview px-3" :style="{backgroundColor, color: textColor}">
    <slot>
      {{ backgroundColor }}
    </slot>
  </span>
</template>

<style scoped lang="scss">
  .color-preview {
    border-radius: 3px;
    border: 1px solid rgba(0, 0, 0, 0.1);
  }
</style>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {inject, onBeforeMount} from 'vue';

  defineProps({disabled: Boolean});

  const dialog = inject('dialog');

  onBeforeMount(() => dialog.setOpened(false));
</script>

<template>
  <a @click="dialog.open()" :class="{disabled}">
    <slot />
  </a>
</template>

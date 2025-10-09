<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {inject, onBeforeMount} from 'vue';

  const dialog = inject('dialog');

  onBeforeMount(() => dialog.setOpened(false));
</script>

<template>
  <a @click="dialog.open()">
    <slot />
  </a>
</template>

<script setup>
  import {inject} from 'vue';

  const dropdownMenu = inject('dropdownMenu');
</script>

<template>
  <ul class="dropdown-menu" @click="dropdownMenu.hide()">
    <slot />
  </ul>
</template>

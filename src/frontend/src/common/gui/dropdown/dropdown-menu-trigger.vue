<script setup>
  import {inject} from "vue";

  const defineProps = defineProps({disabled: Boolean});

  const dropdownMenu = inject("dropdownMenu");
</script>

<template>
  <a class="dropdown-toggle" @click="dropdownMenu.toggle()" :class="{disabled}">
    <slot/>
    {{ ' ' }}
    <span class="caret"></span>
  </a>
</template>

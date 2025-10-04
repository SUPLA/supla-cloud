<script setup>
  import {inject} from "vue";

  const dropdownMenu = inject("dropdownMenu");
</script>

<template>
  <a class="dropdown-toggle" @click="dropdownMenu.toggle()">
    <slot/>
    {{ ' ' }}
    <span class="caret"></span>
  </a>
</template>

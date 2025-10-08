<template>
  <span v-if="app.connected !== undefined" :class="'label ' + (app.connected ? 'label-success' : 'label-grey')">
    {{ $t(app.connected ? 'Active' : 'Idle') }}
  </span>
</template>

<script setup>
  defineProps({app: Object});
</script>

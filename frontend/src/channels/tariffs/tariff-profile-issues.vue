<template>
  <ul v-if="messages.length" :class="['issue-list', `issue-list--${variant}`]">
    <li v-for="message in messages" :key="`${variant}-${message}`">{{ message }}</li>
  </ul>
</template>

<script setup>
  defineProps({
    messages: {
      type: Array,
      default: () => [],
    },
    variant: {
      type: String,
      default: 'danger',
    },
  });
</script>

<style scoped>
  .issue-list {
    padding-left: 18px;
    margin: 16px 0 0;
  }

  .issue-list--danger {
    color: #b04c56;
  }

  .issue-list--warning {
    color: #9a6a17;
  }
</style>

<script setup>
  import {computed, inject} from 'vue';

  const pageTitle = inject('pageTitle');

  const props = defineProps({current: [String, Boolean]});

  const currentPageTitle = computed(() => props.current || pageTitle.value);
</script>

<template>
  <div class="breadcrumbs mb-3">
    <slot name="alt" />
    <div class="breadcrumbs-list">
      <slot />
      <span v-if="current !== false">{{ currentPageTitle }}</span>
    </div>
  </div>
</template>

<style scoped lang="scss">
  @use '@/styles/variables' as *;

  .breadcrumbs {
    font-family: $supla-font-special;
    text-transform: uppercase;

    .breadcrumbs-list > * {
      &:not(:last-child)::after {
        color: $supla-black;
        display: inline-block;
        margin: 0 1em;
        content: '>';
        font-weight: 100;
      }
    }
  }
</style>

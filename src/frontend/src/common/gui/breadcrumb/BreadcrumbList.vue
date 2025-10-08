<script setup>
  import {computed, inject} from "vue";

  const pageTitle = inject("pageTitle");

  const props = defineProps({current: String});

  const currentPageTitle = computed(() => props.current || pageTitle.value);
</script>

<template>
  <div class="container">
    <div class="breadcrumbs">
      <slot/>
      <span>{{ currentPageTitle }}</span>
    </div>
  </div>
</template>

<style scoped lang="scss">
  @use '@/styles/variables' as *;

  .breadcrumbs {
    margin-bottom: 1em;
    font-family: $supla-font-special;
    text-transform: uppercase;

    > * {
      &:not(:last-child)::after {
        color: $supla-black;
        display: inline-block;
        margin: 0 1em;
        content: ">";
        font-weight: 100;
      }
    }
  }
</style>

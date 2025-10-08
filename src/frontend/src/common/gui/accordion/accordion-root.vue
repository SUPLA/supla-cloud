<script setup>
  import {computed, provide, ref} from 'vue';

  const props = defineProps({
    multiple: Boolean,
  });

  const openItems = ref([]);
  const toggleItem = (name) => {
    if (props.multiple) {
      if (openItems.value.includes(name)) {
        openItems.value = openItems.value.filter((item) => item !== name);
      } else {
        openItems.value.push(name);
      }
    } else {
      openItems.value = openItems.value.includes(name) ? [] : [name];
    }
  };
  provide('accordion', {
    openItems,
    toggleItem,
    props: computed(() => props),
  });
</script>

<template>
  <div class="accordion-wrapper">
    <slot />
  </div>
</template>

<style scoped lang="scss"></style>

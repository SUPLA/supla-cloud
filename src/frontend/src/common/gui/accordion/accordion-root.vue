<script setup>
  import {computed, provide, ref} from 'vue';

  const props = defineProps({
    multiple: Boolean,
  });

  const openItems = ref([]);
  const toggleItem = (name, openOrClose = undefined) => {
    const isOpened = openItems.value.includes(name);
    const action = openOrClose === undefined ? !isOpened : openOrClose;
    if (props.multiple) {
      if (!action) {
        openItems.value = openItems.value.filter((item) => item !== name);
      } else if (!isOpened) {
        openItems.value.push(name);
      }
    } else {
      openItems.value = action ? [] : [name];
    }
  };
  provide('accordion', {
    openItems,
    toggleItem,
    props: computed(() => props),
  });
</script>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<template>
  <div class="accordion-wrapper">
    <slot />
  </div>
</template>

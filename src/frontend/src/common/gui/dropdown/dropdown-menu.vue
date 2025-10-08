<script setup>
  import {provide, ref} from "vue";

  const props = defineProps({
    tag: {
      type: String,
      default: "div",
    },
    disabled: Boolean,
  })

  const isOpened = ref(false);

  provide("dropdownMenu", {
    isOpened,
    disabled: props.disabled,
    toggle: () => isOpened.value = !isOpened.value,
    hide: () => isOpened.value = false,
  });
</script>

<template>
  <Component :is="tag" class="dropdown" :class="{open: isOpened}">
    <slot/>
  </Component>
</template>

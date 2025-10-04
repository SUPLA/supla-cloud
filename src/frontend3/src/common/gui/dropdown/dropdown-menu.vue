<script setup>
  import {provide, ref, useTemplateRef} from "vue";
  import {onClickOutside} from "@vueuse/core";

  defineProps({
    tag: {
      type: String,
      default: "div",
    }
  })

  const isOpened = ref(false);

  const target = useTemplateRef('target');
  onClickOutside(target, () => isOpened.value = false);

  provide("dropdownMenu", {
    isOpened,
    toggle: () => isOpened.value = !isOpened.value,
  });
</script>

<template>
  <Component :is="tag" class="dropdown" :class="{open: isOpened}" ref="target">
    <slot/>
  </Component>
</template>

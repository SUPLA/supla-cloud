<script setup>
  import {computed, provide} from 'vue';

  const props = defineProps({
    multiple: Boolean,
    disabled: Boolean,
  });

  const model = defineModel();

  const openItems = computed({
    get() {
      if (props.multiple) {
        return Array.isArray(model.value) ? model.value : [];
      }
      return model.value ? [model.value] : [];
    },
    set(val) {
      if (props.multiple) {
        model.value = val;
      } else {
        model.value = val[0] ?? null;
      }
    },
  });

  function toggleItem(name) {
    if (props.disabled) {
      return;
    }
    const isOpen = openItems.value.includes(name);
    if (props.multiple) {
      openItems.value = isOpen ? openItems.value.filter((n) => n !== name) : [...openItems.value, name];
    } else {
      openItems.value = isOpen ? [] : [name];
    }
  }

  provide('accordion', {
    openItems,
    toggleItem,
    disabled: computed(() => props.disabled),
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

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed, provide, readonly, ref} from 'vue';

  const props = defineProps({
    warning: Boolean,
    cancellable: Boolean,
  });

  const emit = defineEmits(['confirm']);

  const opened = defineModel({default: true, type: Boolean});
  const setOpened = (op) => {
    opened.value = op;
    if (!op) {
      setLoading(false);
    }
  };

  const loading = ref(false);
  const setLoading = (op) => (loading.value = op);

  const dialogCssClasses = computed(() => ({
    'dialog-warning': props.warning,
  }));

  const dialog = {
    opened: readonly(opened),
    loading: readonly(loading),
    cancellable: readonly(props.cancellable),
    dialogCssClasses,
    setOpened,
    setLoading,
    open: () => setOpened(true),
    close: () => setOpened(false),
    confirm: () => {
      if (props.cancellable) {
        setLoading(true);
        emit('confirm', dialog);
      } else {
        dialog.close();
      }
    },
  };

  provide('dialog', dialog);
</script>

<template>
  <slot />
</template>

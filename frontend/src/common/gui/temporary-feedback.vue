<template>
  <TransitionExpand>
    <div v-if="visible" v-bind="$attrs" class="alert alert-info mt-2 mb-0">
      <button type="button" class="close temporary-feedback-close ml-2" :aria-label="$t('Close')" @click="dismiss">
        <fa :icon="faTimes" />
      </button>
      {{ messageI18n ? $t(messageI18n) : message }}
    </div>
  </TransitionExpand>
</template>

<script setup>
  import {onBeforeUnmount, ref, watch} from 'vue';
  import {faTimes} from '@fortawesome/free-solid-svg-icons';
  import TransitionExpand from '@/common/gui/transition-expand.vue';

  defineOptions({compatConfig: {MODE: 3}, inheritAttrs: false});
  const props = defineProps({
    message: {type: String, default: ''},
    messageI18n: {type: String, default: ''},
    show: {type: Boolean, default: undefined},
    timeout: {type: Number, default: 3000},
  });
  const visible = ref(false);
  let timer;

  function dismiss() {
    clearTimeout(timer);
    visible.value = false;
  }

  watch(
    () => [props.message, props.messageI18n, props.show],
    ([message, messageI18n, show]) => {
      clearTimeout(timer);
      visible.value = !!(messageI18n || message) && show !== false;
      if (visible.value) {
        timer = setTimeout(() => (visible.value = false), props.timeout);
      }
    },
    {immediate: true}
  );

  onBeforeUnmount(() => clearTimeout(timer));
</script>

<style scoped>
  .temporary-feedback-close {
    font-size: 1em;
  }
</style>

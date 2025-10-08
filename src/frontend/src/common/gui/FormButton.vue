<script setup>
  import {computed} from 'vue';
  import ButtonLoadingDots from '@/common/gui/loaders/button-loading-dots.vue';

  const props = defineProps({
    loading: Boolean,
    disabled: Boolean,
    disabledReason: String,
    submit: Boolean,
    buttonClass: String,
  });

  defineEmits(['click']);

  const buttonType = computed(() => (props.submit ? 'submit' : 'button'));
  const isDisabled = computed(() => !!(props.loading || props.disabled || props.disabledReason));
</script>

<template>
  <span v-tooltip="disabledReason ? $t(disabledReason) : ''">
    <button :type="buttonType" :class="['btn', buttonClass, {disabled: isDisabled}]" :disabled="isDisabled" @click="$emit('click')">
      <slot v-if="loading" name="loading">
        <button-loading-dots />
      </slot>
      <slot v-else />
    </button>
  </span>
</template>

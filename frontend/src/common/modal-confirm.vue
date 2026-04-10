<template>
  <modal class="modal-confirm" :header="header">
    <slot></slot>
    <template #footer>
      <button-loading-dots v-if="loading"></button-loading-dots>
      <div v-else>
        <a v-if="cancellable" class="cancel" @click="$emit('cancel')">
          <i class="pe-7s-close"></i>
        </a>
        <a v-if="confirmable" class="confirm" @click="$emit('confirm')">
          <i class="pe-7s-check"></i>
        </a>
      </div>
    </template>
  </modal>
</template>

<script setup>
  import Modal from '@/common/modal.vue';
  import ButtonLoadingDots from '@/common/gui/loaders/button-loading-dots.vue';
  import {onKeyStroke} from '@vueuse/core';

  const props = defineProps({
    header: String,
    loading: {
      type: Boolean,
      required: false,
    },
    cancellable: {
      type: Boolean,
      required: false,
      default: true,
    },
    confirmable: {
      type: Boolean,
      default: true,
    },
  });

  const emit = defineEmits(['cancel', 'confirm']);

  onKeyStroke('Escape', () => !props.loading && emit('cancel'));
  onKeyStroke('Enter', () => !props.loading && emit('confirm'));
</script>

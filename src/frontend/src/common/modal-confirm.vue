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

<script>
  import Modal from '@/common/modal.vue';
  import ButtonLoadingDots from '@/common/gui/loaders/button-loading-dots.vue';

  export default {
    components: {ButtonLoadingDots, Modal},
    props: {
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
    },
  };
</script>

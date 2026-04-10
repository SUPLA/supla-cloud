<template>
  <span>
    <span v-if="model?.state?.connectedCode === 'CONNECTED_NOT_AVAILABLE'" class="label label-warning mr-2">{{ $t('Not available') }}</span>
    <span v-if="model?.state?.connectedCode === 'FIRMWARE_UPDATE_ONGOING'" class="label label-info mr-2">{{ $t('Updating...') }}</span>
    <span v-if="model?.state?.connectedCode === 'OFFLINE_REMOTE_WAKEUP_NOT_SUPPORTED'" class="label label-warning mr-2">
      <fa icon="exclamation-triangle" />
    </span>
    <span v-if="model && model.connected !== undefined" :class="'label ' + (model.connected ? 'label-success' : 'label-danger')">
      {{ model.connected ? $t('Connected') : $t('Disconnected') }}
    </span>
  </span>
</template>

<script setup>
  defineProps({
    model: Object,
  });
</script>

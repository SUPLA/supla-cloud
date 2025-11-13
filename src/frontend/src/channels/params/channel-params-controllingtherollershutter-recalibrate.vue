<template>
  <div v-if="channel.config.recalibrateAvailable" v-tooltip="calibrationDisabledReason ? $t(calibrationDisabledReason) : ''">
    <DialogWindow warning cancellable @confirm="(dialog) => calibrate(dialog)">
      <DialogTrigger class="btn btn-default btn-block" :disabled="!!calibrationDisabledReason">{{ $t('Calibrate') }}</DialogTrigger>
      <DialogContent>
        <template #header>
          <h4>{{ $t('Are you sure?') }}</h4>
        </template>
        {{ $t('Confirm if you want to remove Access Identifier') }}
      </DialogContent>
    </DialogWindow>
  </div>
</template>

<script setup>
  import {computed, watch} from 'vue';
  import {successNotification} from '@/common/notifier';
  import {api} from '@/api/api.js';
  import {useChannelsStore} from '@/stores/channels-store.js';
  import {DialogContent, DialogTrigger, DialogWindow} from '@/common/gui/dialog/index.js';

  const props = defineProps(['channel']);

  const channelsStore = useChannelsStore();
  const channel = computed(() => channelsStore.all[props.channel.id]);
  const channelState = computed(() => channel.value?.state || {});

  const isConnected = computed(() => channelState.value.connectedCode === 'CONNECTED');
  const notCalibrated = computed(() => channelState.value.not_calibrated);

  const calibrationDisabledReason = computed(() => {
    if (props.channel.hasPendingChanges) {
      return 'Save or discard configuration changes first.'; // i18n
    } else if (!isConnected.value) {
      return 'Device is disconnected.'; // i18n
    } else {
      return '';
    }
  });

  watch(notCalibrated, (newState, oldState) => {
    if (!newState && oldState === true && isConnected.value) {
      successNotification(props.$t('Calibration successful.'));
    }
  });

  const calibrate = (dialog) => {
    api
      .patch('channels/' + props.channel.id + '/settings', {action: 'recalibrate'})
      .then(() => setTimeout(() => dialog.close(), 5000))
      .catch(() => dialog.setLoading(false));
  };
</script>

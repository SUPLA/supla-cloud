<template>
  <div v-if="channel.config.recalibrateAvailable" v-tooltip="calibrationDisabledReason ? $t(calibrationDisabledReason) : ''">
    <DialogWindow warning cancellable @confirm="(dialog) => calibrate(dialog)">
      <DialogTrigger class="btn btn-default btn-block" :disabled="!!calibrationDisabledReason">{{ $t('Calibrate') }}</DialogTrigger>
      <DialogContent>
        <template #header>
          <h4>{{ $t('Are you sure?') }}</h4>
        </template>
        {{ $t('Confirm if you want to perform the calibration.') }}
      </DialogContent>
    </DialogWindow>
  </div>
</template>

<script setup>
  import {computed} from 'vue';
  import {api} from '@/api/api.js';
  import {useChannelsStore} from '@/stores/channels-store.js';
  import {DialogContent, DialogTrigger, DialogWindow} from '@/common/gui/dialog/index.js';
  import {successNotification} from '@/common/notifier.js';

  const props = defineProps(['channel']);

  const channelsStore = useChannelsStore();
  const channel = computed(() => channelsStore.all[props.channel.id]);
  const channelState = computed(() => channel.value?.state || {});
  const isConnected = computed(() => channelState.value.connectedCode === 'CONNECTED');

  const calibrationDisabledReason = computed(() => {
    if (props.channel.hasPendingChanges) {
      return 'Save or discard configuration changes first.'; // i18n
    } else if (!isConnected.value) {
      return 'Device is disconnected.'; // i18n
    } else {
      return '';
    }
  });

  const calibrate = (dialog) => {
    api
      .patch('channels/' + props.channel.id + '/settings', {action: 'recalibrate'})
      .then(() => {
        dialog.close();
        successNotification('Calibration request has been sent.'); // i18n
      })
      .catch(() => dialog.setLoading(false));
  };
</script>

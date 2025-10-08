<template>
  <div v-if="!device.locked && device.flags.automaticFirmwareUpdatesSupported">
    <div v-if="device.config.otaUpdate?.status === 'AVAILABLE'">
      <div class="alert alert-info text-left my-1">
        <div>{{ $t('Firmware update is available.') }}</div>
        <div>{{ $t('New version') }}: {{ device.config.otaUpdate.version || '?' }}</div>
        <div v-if="updateUrl">
          {{ $t('Details') }}:
          <a :href="updateUrl">{{ updateUrl }}</a>
        </div>
      </div>
    </div>
    <div v-else-if="device.config.otaUpdate?.status === 'NOT_AVAILABLE'">
      <div class="alert alert-info my-1">
        <div>{{ $t('You have the newest firmware installed.') }}</div>
      </div>
    </div>
    <div class="d-flex mt-2">
      <FormButton
        :disabled-reason="disabledReason"
        :loading="isCheckingUpdates"
        button-class="btn-default btn-xs btn-block"
        class="flex-basis-50 pr-1"
        @click="checkUpdates()"
      >
        {{ $t('Check available updates') }}
      </FormButton>
      <FormButton
        :disabled-reason="disabledReason"
        :loading="isPerformingUpdate"
        button-class="btn-default btn-xs btn-block"
        class="flex-basis-50 pl-1"
        @click="updateConfirm = true"
      >
        {{ $t('Perform firmware update') }}
      </FormButton>
    </div>
    <modal-confirm
      v-if="updateConfirm"
      class="modal-warning"
      :loading="isPerformingUpdate"
      :header="$t('Are you sure?')"
      @confirm="performUpdate()"
      @cancel="updateConfirm = false"
    >
      <p>{{ $t('This action will result in trying to update the device firmware. It will not be available for a while.') }}</p>
    </modal-confirm>
  </div>
</template>

<script setup>
  import {computed, ref} from 'vue';
  import {useDevicesStore} from '@/stores/devices-store';
  import {devicesApi} from '@/api/devices-api';
  import {promiseTimeout} from '@vueuse/core';
  import FormButton from '@/common/gui/FormButton.vue';
  import {errorNotification, successNotification} from '@/common/notifier';
  import {waitForDeviceOperation} from '@/devices/details/device-details-helpers';
  import ModalConfirm from '@/common/modal-confirm.vue';

  const props = defineProps({device: Object});
  const devicesStore = useDevicesStore();

  const disabledReason = computed(() => (props.device.connected ? '' : 'Device is disconnected.'));

  const updateConfirm = ref(false);
  const isPerformingUpdate = ref(false);
  const isCheckingUpdates = ref(false);
  const updateUrl = computed(() => {
    const url = props.device?.config.otaUpdate?.url;
    if (url) {
      return url.startsWith('http') ? url : 'https://updates.supla.org' + url;
    } else {
      return '';
    }
  });

  async function checkUpdates() {
    isCheckingUpdates.value = true;
    try {
      await devicesApi.otaCheckUpdates(props.device.id);
      await promiseTimeout(1000);
      await devicesStore.fetchDevice(props.device.id);
      await waitForDeviceOperation(() => ['AVAILABLE', 'NOT_AVAILABLE'].includes(props.device.config?.otaUpdate?.status));
    } catch (error) {
      errorNotification('Error', 'Could not check updates.'); // i18n
    } finally {
      isCheckingUpdates.value = false;
    }
  }

  async function performUpdate() {
    isPerformingUpdate.value = true;
    try {
      await devicesApi.otaPerformUpdate(props.device.id);
      await promiseTimeout(5000);
      await devicesStore.fetchDevice(props.device.id);
      successNotification('Successful', 'Device should be checking and installing updates.'); // i18n
    } finally {
      isPerformingUpdate.value = false;
      updateConfirm.value = false;
    }
  }
</script>

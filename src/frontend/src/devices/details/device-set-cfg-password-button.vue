<template>
  <div v-if="device.flags.setCfgModePasswordSupported" v-tooltip="disabledReason">
    <button class="btn btn-default btn-block btn-wrapped" type="button" :disabled="!!disabledReason" @click="showDialog = true">
      {{ $t('Set the configuration password') }}
    </button>
    <modal-confirm
      v-if="showDialog"
      :header="$t('Set the configuration password')"
      :loading="loading"
      @cancel="showDialog = false"
      @confirm="setDevicePassword()"
    >
      <p>{{ $t('Set the password that restricts access to the device configuration page (https://192.168.4.1).') }}</p>
      <p>{{ $t('Password should be at least 8 characters long and contain at least one uppercase, one lowercase letter and one number.') }}</p>
      <div class="form-group">
        <label>{{ $t('New password') }}</label>
        <input v-model="password" type="password" class="form-control" />
      </div>
      <div class="form-group">
        <label>{{ $t('Confirm new password') }}</label>
        <input v-model="passwordConfirmation" type="password" class="form-control" />
      </div>
    </modal-confirm>
  </div>
</template>

<script setup>
  import {computed, ref} from 'vue';
  import {errorNotification, successNotification} from '@/common/notifier';
  import {devicesApi} from '@/api/devices-api';
  import {promiseTimeout} from '@vueuse/core';
  import {waitForDeviceOperation} from '@/devices/details/device-details-helpers';
  import {useDevicesStore} from '@/stores/devices-store';
  import ModalConfirm from '@/common/modal-confirm.vue';

  const props = defineProps({device: Object});

  const showDialog = ref(false);
  const loading = ref(false);

  const password = ref('');
  const passwordConfirmation = ref('');
  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;

  const disabledReason = computed(() => (props.device.connected ? '' : 'Device is disconnected.'));

  async function setDevicePassword() {
    if (password.value !== passwordConfirmation.value) {
      return errorNotification('Error', 'Passwords do not match.' /*i18n*/);
    }
    if (password.value.length < 8 || !passwordRegex.test(password.value)) {
      return errorNotification(
        'Error',
        'Password should be at least 8 characters long and contain at least one uppercase, one lowercase letter and one number.' /*i18n*/
      );
    }
    loading.value = true;
    try {
      await devicesApi.setCfgModePassword(props.device.id, password.value);
      await promiseTimeout(1000);
      await useDevicesStore().fetchDevice(props.device.id);
      await waitForDeviceOperation(() => ['TRUE', 'FALSE'].includes(props.device.config?.setCfgModePassword?.status));
      if (props.device.config?.setCfgModePassword?.status !== 'TRUE') {
        throw new Error();
      }
      successNotification('Success', 'Password was set successfully.' /*i18n*/);
      showDialog.value = false;
      password.value = '';
      passwordConfirmation.value = '';
    } catch (error) {
      errorNotification('Error', 'Could not change the password.' /*i18n*/);
    } finally {
      loading.value = false;
    }
  }
</script>

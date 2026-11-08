<template>
  <div>
    <DeviceEnterConfigurationModeButton :device="deviceWithPendingChanges" class="mb-2" />
    <DeviceIdentifyDeviceButton :device="deviceWithPendingChanges" class="mb-2" />
    <DeviceRemoteRestartButton :device="deviceWithPendingChanges" class="mb-2" />
    <DeviceRemoteFactoryResetButton :device="deviceWithPendingChanges" class="mb-2" />
    <DeviceSetCfgPasswordButton :device="deviceWithPendingChanges" class="mb-2" />
  </div>
</template>

<script setup>
  defineOptions({compatConfig: {MODE: 3}});

  import DeviceEnterConfigurationModeButton from '@/devices/details/device-enter-configuration-mode-button.vue';
  import DeviceIdentifyDeviceButton from '@/devices/details/device-identify-device-button.vue';
  import DeviceRemoteRestartButton from '@/devices/details/device-remote-restart-button.vue';
  import DeviceRemoteFactoryResetButton from '@/devices/details/device-remote-factory-reset-button.vue';
  import DeviceSetCfgPasswordButton from '@/devices/details/device-set-cfg-password-button.vue';

  import {computed} from 'vue';

  const props = defineProps({device: Object, hasPendingChanges: Boolean});
  const deviceWithPendingChanges = computed(() => ({...props.device, hasPendingChanges: props.hasPendingChanges || props.device.hasPendingChanges}));
</script>

<template>
  <div class="container">
    <div class="row">
      <div class="col-lg-4 col-sm-6">
        <div class="details-page-block">
          <h3 class="text-center">{{ $t('Settings') }}</h3>
          <DeviceDetailsTabSettings :device="device" />
        </div>
      </div>
      <div class="col-lg-4 col-sm-6">
        <div class="details-page-block">
          <h3 class="text-center">{{ $t('Default channels location') }}</h3>
          <DeviceDetailsTabLocation :device="device" />
        </div>
      </div>
      <div class="col-lg-4 col-sm-6">
        <div class="details-page-block">
          <h3 class="text-center">{{ $t('Status') }}</h3>
          <DeviceDetailsTabFirmwareInfo :device="device" />
        </div>
        <div v-if="hasRemoteAccessButtons" class="details-page-block">
          <h3 class="text-center">{{ $t('Actions') }}</h3>
          <DeviceDetailsTabRemoteButtons :device="device" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import DeviceDetailsTabFirmwareInfo from '@/devices/details/device-details-tab-firmware-info.vue';
  import DeviceDetailsTabLocation from '@/devices/details/device-details-tab-location.vue';
  import DeviceDetailsTabSettings from '@/devices/details/device-details-tab-settings.vue';
  import DeviceDetailsTabRemoteButtons from '@/devices/details/device-details-tab-remote-buttons.vue';
  import {computed} from 'vue';

  const props = defineProps({device: Object});

  const hasRemoteAccessButtons = computed(
    () =>
      !props.device.locked &&
      (props.device.flags.enterConfigurationModeAvailable ||
        props.device.flags.identifyDeviceAvailable ||
        props.device.flags.remoteRestartAvailable ||
        props.device.flags.factoryResetSupported ||
        props.device.flags.setCfgModePasswordSupported)
  );
</script>

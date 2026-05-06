<script setup>
  import {computed} from 'vue';
  import {useSubDevicesStore} from '@/stores/subdevices-store';
  import {subDevicesApi} from '@/api/subdevices-api';
  import PromiseConfirmButton from '@/devices/details/promise-confirm-button.vue';
  import ChannelDeleteButton from '@/channels/channel-delete-button.vue';

  const props = defineProps({
    channels: Array,
  });

  const subDevices = useSubDevicesStore();
  subDevices.fetchAll();
  const subDevice = computed(() => subDevices.forChannel(props.channels[0]));

  const identify = () => subDevicesApi.identify(props.channels[0]);
  const identifyAvailable = computed(() => props.channels[0]?.config?.identifySubdeviceAvailable);

  const restart = () => subDevicesApi.restart(props.channels[0]);
  const restartAvailable = computed(() => props.channels[0]?.config?.restartSubdeviceAvailable);

  const deleteAvailable = computed(() => !props.channels.find((ch) => !ch.deletable));
</script>

<template>
  <div>
    <h3 v-if="subDevice && subDevice.name" class="mt-3 mb-2">{{ subDevice.name }}</h3>
    <h3 v-else class="mt-3 mb-2">{{ $t('Subdevice #{id}', {id: channels[0].subDeviceId}) }}</h3>
    <div v-if="identifyAvailable || restartAvailable || deleteAvailable" class="mb-3 d-flex">
      <PromiseConfirmButton v-if="identifyAvailable" :action="identify" label-i18n="Identify device" class="mr-2" />
      <PromiseConfirmButton v-if="restartAvailable" :action="restart" label-i18n="Restart device" class="mr-2" />
      <ChannelDeleteButton v-if="deleteAvailable" :channel="channels[0]" deleting-subdevice />
    </div>
    <div v-if="subDevice" class="mb-3">
      <span v-if="subDevice.softwareVersion" class="label label-default mr-2"> {{ $t('Firmware') }}: {{ subDevice.softwareVersion }} </span>
      <span v-if="subDevice.productCode" class="label label-default mr-2">{{ $t('P/C') }}: {{ subDevice.productCode }}</span>
      <span v-if="subDevice.serialNumber" class="label label-default mr-2">{{ $t('S/N') }}: {{ subDevice.serialNumber }}</span>
    </div>
  </div>
</template>

import {defineStore} from 'pinia';
import {useFetchList} from '@/stores/index.js';
import {subDevicesApi} from '@/api/subdevices-api.js';

export const useSubDevicesStore = defineStore('subDevices', () => {
  const {all, $reset, fetchAll} = useFetchList(subDevicesApi, {idFactory: (item) => `${item.id}_${item.ioDeviceId}`});
  const forChannel = (channel) => all.value[`${channel?.subDeviceId}_${channel?.iodeviceId}`];
  return {forChannel, $reset, fetchAll};
});

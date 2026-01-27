import {defineStore} from 'pinia';
import {subDevicesApi} from '@/api/subdevices-api';
import {useFetchList} from '@/stores/index.js';

export const useSubDevicesStore = defineStore('subDevices', () => {
  const {all, $reset, fetchAll} = useFetchList(subDevicesApi.getList);
  const forChannel = (channel) => all.value[`${channel?.subDeviceId}_${channel?.iodeviceId}`];
  return {forChannel, $reset, fetchAll};
});

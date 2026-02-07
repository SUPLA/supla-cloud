import {defineStore} from 'pinia';
import {devicesApi} from '@/api/devices-api';
import {useChannelsStore} from '@/stores/channels-store';
import {useFetchList} from '@/stores/index.js';

export const useDevicesStore = defineStore('devices', () => {
  const {all, ids, list, ready, $reset, fetchAll} = useFetchList(devicesApi.getList);

  const updateStates = (devicesStates) => {
    let refetch = false;
    const idsToFetch = [];
    devicesStates.forEach((device) => {
      if (all.value[device.id]) {
        all.value[device.id].connected = device.connected;
        if (all.value[device.id].checksum !== device.checksum) {
          idsToFetch.push(device.id);
        }
      } else {
        refetch = true;
      }
    });
    if (refetch || idsToFetch.length > 5) {
      fetchAll(true);
    } else {
      idsToFetch.forEach((id) => fetchDevice(id));
    }
  };

  const remove = (deviceId, safe = true) => {
    return devicesApi.remove(deviceId, safe).then(() => {
      delete all.value[deviceId];
      ids.value = ids.value.filter((id) => id !== deviceId);
      return useChannelsStore().refetchAll();
    });
  };

  const updateDevice = (device) => {
    all.value[device.id] = {...all.value[device.id], ...device};
  };

  const fetchDevice = (deviceId) => {
    return devicesApi.getOne(deviceId).then((device) => {
      updateDevice(device);
      return device;
    });
  };

  return {all, ids, list, ready, $reset, fetchAll, updateStates, remove, fetchDevice};
});

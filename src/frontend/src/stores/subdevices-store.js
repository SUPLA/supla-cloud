import {defineStore} from "pinia";
import {ref} from "vue";
import {subDevicesApi} from "@/api/subdevices-api";

export const useSubDevicesStore = defineStore('subDevices', () => {
    const all = ref({});
    const ids = ref([]);

    const fetchAll = () => {
        if (fetchAll.promise) {
            return fetchAll.promise;
        } else {
            return fetchAll.promise = subDevicesApi.getList().then((subDevices) => {
                const state = subDevices.reduce((acc, curr) => {
                    const id = `${curr.id}_${curr.ioDeviceId}`;
                    return {
                        ids: acc.ids.concat(id),
                        all: {...acc.all, [id]: curr}
                    }
                }, {ids: [], all: {}});
                all.value = state.all;
                ids.value = state.ids;
            })
        }
    };

    const forChannel = (channel) => all.value[`${channel?.subDeviceId}_${channel?.iodeviceId}`];

    const $reset = () => {
        all.value = {};
        ids.value = [];
        fetchAll.promise = undefined;
    };

    return {forChannel, $reset, fetchAll};
});

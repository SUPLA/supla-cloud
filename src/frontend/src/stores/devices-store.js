import {defineStore} from "pinia";
import {computed, ref} from "vue";
import {devicesApi} from "@/api/devices-api";
import {useChannelsStore} from "@/stores/channels-store";

export const useDevicesStore = defineStore('devices', () => {
    const all = ref({});
    const ids = ref([]);

    const fetchAll = (force = false) => {
        if (fetchAll.promise && !force) {
            return fetchAll.promise;
        } else {
            return fetchAll.promise = devicesApi.getList().then((devices) => {
                const state = devices.reduce((acc, curr) => {
                    return {
                        ids: acc.ids.concat(curr.id),
                        all: {...acc.all, [curr.id]: curr}
                    }
                }, {ids: [], all: {}});
                all.value = state.all;
                ids.value = state.ids;
            })
        }
    };

    const updateConnectedStatuses = (channelsStates) => {
        const state = channelsStates.reduce((acc, curr) => {
            acc[curr.iodeviceId] = !!(acc[curr.iodeviceId] || curr.state.connected);
            return acc;
        }, {});
        Object.keys(state).forEach((deviceId) => {
            all.value[deviceId].connected = state[deviceId];
        });
    };

    const remove = (deviceId, safe = true) => {
        return devicesApi.remove(deviceId, safe)
            .then(() => {
                delete all.value[deviceId];
                ids.value = ids.value.filter((id) => id !== deviceId);
                const channelsStore = useChannelsStore();
                channelsStore.refetchAll();
            });
    }

    const list = computed(() => ids.value.map(id => all.value[id]));

    const $reset = () => {
        all.value = {};
        ids.value = [];
        fetchAll.promise = undefined;
    };

    return {all, ids, list, $reset, fetchAll, updateConnectedStatuses, remove};
});

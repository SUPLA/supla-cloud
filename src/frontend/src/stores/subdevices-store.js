import {defineStore} from "pinia";
import {ref} from "vue";
import {subDevicesApi} from "@/api/subdevices-api";

export const useSubDevicesStore = () => {
    const innerStore = defineStore('subDevices', () => {
        const all = ref({});
        const ids = ref([]);
        const isFetching = ref(false);
        const fetchedData = ref(undefined);

        const fetchAll = async () => {
            isFetching.value = true;
            const subDevices = await subDevicesApi.getList();
            const state = subDevices.reduce((acc, curr) => {
                return {
                    ids: acc.ids.concat(curr.id),
                    all: {...acc.all, [curr.id]: curr}
                }
            }, {ids: [], all: {}});
            all.value = state.all;
            ids.value = state.ids;
            fetchedData.value = subDevices;
            isFetching.value = false;
        };

        const $reset = () => {
            all.value = {};
            ids.value = [];
            fetchedData.value = undefined;
        };

        return {fetchedData, all, ids, isFetching, $reset, fetchAll};
    });

    const store = innerStore();

    if (!store.fetchedData && !store.isFetching) {
        store.fetchAll();
    }

    return store;
};

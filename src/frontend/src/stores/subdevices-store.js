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

    const $reset = () => {
        all.value = {};
        ids.value = [];
        fetchAll.promise = undefined;
    };

    return {all, ids, $reset, fetchAll};
});

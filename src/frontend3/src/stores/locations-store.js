import {defineStore} from "pinia";
import {ref} from "vue";
import {locationsApi} from "@/api/locations-api";

export const useLocationsStore = defineStore('locations', () => {
    const all = ref({});
    const ids = ref([]);

    const fetchAll = (force = false) => {
        if (fetchAll.promise && !force) {
            return fetchAll.promise;
        } else {
            return fetchAll.promise = locationsApi.getList().then((locations) => {
                const state = locations.reduce((acc, curr) => {
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

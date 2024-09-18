import {defineStore} from "pinia";
import {useSuplaApi} from "@/common/use-supla-api";
import {ref} from "vue";

export const useSubDevicesStore = () => {
    const innerStore = defineStore('subDevices', () => {
        const {data, execute, isFetching} = useSuplaApi('subdevices', {immediate: false}).json();

        const subDevices = ref([]);

        const fetchSubdevices = async () => {
            await execute();
            subDevices.value = data.value;
        };

        const $reset = () => {
            subDevices.value = [];
            data.value = undefined;
        };

        return {data, subDevices, isFetching, $reset, fetchSubdevices};
    });

    const store = innerStore();

    if (!store.data && !store.isFetching) {
        store.fetchSubdevices();
    }

    return store;
};

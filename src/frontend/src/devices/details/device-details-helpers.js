import {computed, ref, watch} from "vue";
import {useDevicesStore} from "@/stores/devices-store";
import {useTimeoutPoll} from "@vueuse/core/index";

export const useDeviceSettingsForm = (deviceId, emit, configGetter) => {
    const devicesStore = useDevicesStore();
    const device = computed(() => devicesStore.all[deviceId])
    const newConfig = ref({});

    const cloneConfig = () => {
        newConfig.value = configGetter(device.value);
    };

    cloneConfig();

    watch(() => device.value, cloneConfig);
    watch(() => newConfig.value, () => emit('change', newConfig.value), {deep: true});

    return {newConfig};
}

export const waitForDeviceOperation = (checker, interval = 3000, maxCheckCount = 10) => {
    let checkCount = 0;
    return new Promise((resolve, reject) => {
        const {pause} = useTimeoutPoll(async () => {
            if (await checker()) {
                pause();
                resolve(await checker());
            } else if (checkCount++ > maxCheckCount) {
                pause();
                reject();
            }
        }, interval, {immediate: true});
    })
}

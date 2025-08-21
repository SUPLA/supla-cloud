import {computed, ref, watch} from "vue";
import {useDevicesStore} from "@/stores/devices-store";

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

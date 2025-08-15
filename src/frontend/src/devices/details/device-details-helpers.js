import {computed, ref, watch} from "vue";
import {devicesApi} from "@/api/devices-api";
import {useDevicesStore} from "@/stores/devices-store";
import {warningNotification} from "@/common/notifier";

export const useDeviceSettingsForm = (deviceId, configGetter) => {
    const devicesStore = useDevicesStore();
    const device = computed(() => devicesStore.all[deviceId])

    const newConfig = ref({});
    const originalConfig = ref({});

    const cloneConfig = () => {
        newConfig.value = configGetter(device.value);
        originalConfig.value = configGetter(device.value);
    };

    cloneConfig();

    async function saveChanges() {
        try {
            await devicesApi.update(deviceId, {
                config: newConfig.value,
                configBefore: originalConfig.value,
            });
        } catch (error) {
            if (error.status === 409) {
                warningNotification(
                    'Settings have not been saved!', // i18n
                    'The configuration has been changed from another source (e.g. another browser tab, mobile app, device). Please adjust the settings and save again.' // i18n
                )
            }
        } finally {
            await devicesStore.fetchDevice(deviceId);
            cloneConfig();
        }
    }

    watch(() => device.value, cloneConfig);

    return {newConfig, originalConfig, cloneConfig, saveChanges};
}

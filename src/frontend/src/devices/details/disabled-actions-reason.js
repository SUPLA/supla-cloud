import {useDevicesStore} from "@/stores/devices-store";
import {useI18n} from "vue-i18n-bridge";
import {computed} from "vue";

export const useDisabledActionsReason = (device) => {
    const devicesStore = useDevicesStore();
    const i18n = useI18n();

    const theDevice = computed(() => devicesStore.all[device.id]);
    const isConnected = computed(() => theDevice.value?.connected);

    const disabledReason = computed(() => {
        if (!isConnected.value) {
            return i18n.t('Device is disconnected.');
        } else if (device.hasPendingChanges) {
            return i18n.t('Save or discard configuration changes first.')
        } else {
            return '';
        }
    });

    return {isConnected, disabledReason, theDevice};
}

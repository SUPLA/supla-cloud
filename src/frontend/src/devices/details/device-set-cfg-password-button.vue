<template>
    <div v-if="device.flags.setCfgModePasswordSupported"
        v-tooltip="disabledReason">
        <button class="btn btn-default btn-block btn-wrapped"
            type="button"
            :disabled="!!disabledReason"
            @click="showDialog = true">
            {{ $t('Set the configuration password') }}
        </button>
        <modal-confirm v-if="showDialog"
            :header="$t('Set the configuration password')"
            :loading="loading"
            @cancel="showDialog = false"
            @confirm="setDevicePassword()">
            <p>{{ $t('This is the password that was configured during the first device setup. It allows you to access the device configuration page when you are connected to its Wi-Fi.') }}</p>
            <div class="form-group">
                <label>{{ $t('New password') }}</label>
                <input type="password" v-model="password" class="form-control">
            </div>
            <div class="form-group">
                <label>{{ $t('Confirm new password') }}</label>
                <input type="password" v-model="passwordConfirmation" class="form-control">
            </div>
        </modal-confirm>
    </div>
</template>

<script setup>
    import {computed, ref} from "vue";
    import {errorNotification, successNotification} from "@/common/notifier";
    import {devicesApi} from "@/api/devices-api";
    import {promiseTimeout} from "@vueuse/core";
    import {waitForDeviceOperation} from "@/devices/details/device-details-helpers";
    import {useDevicesStore} from "@/stores/devices-store";

    const props = defineProps({device: Object});

    const showDialog = ref(false);
    const loading = ref(false);

    const password = ref('');
    const passwordConfirmation = ref('');

    const disabledReason = computed(() => props.device.connected ? '' : 'Device is disconnected.');

    async function setDevicePassword() {
        if (password.value !== passwordConfirmation.value) {
            return errorNotification('Error', 'Passwords do not match.'/*i18n*/);
        }
        if (password.value.length < 5) {
            return errorNotification('Error', 'Password must be at least 5 characters long.'/*i18n*/);
        }
        loading.value = true;
        try {
            await devicesApi.setCfgModePassword(props.device.id, password.value);
            await promiseTimeout(1000);
            await useDevicesStore().fetchDevice(props.device.id);
            await waitForDeviceOperation(() => ['TRUE', 'FALSE'].includes(props.device.config?.setCfgModePassword?.status));
            if (props.device.config?.setCfgModePassword?.status !== 'TRUE') {
                throw new Error();
            }
            successNotification('Success', 'Password was set successfully.'/*i18n*/);
            showDialog.value = false;
            password.value = '';
            passwordConfirmation.value = '';
        } catch (error) {
            errorNotification('Error', 'Could not change the password.'/*i18n*/);
        } finally {
            loading.value = false;
        }
    }
</script>

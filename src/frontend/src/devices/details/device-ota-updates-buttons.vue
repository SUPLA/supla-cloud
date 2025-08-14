<template>
    <div v-if="!device.locked && device.flags.automaticFirmwareUpdatesSupported">
        <div v-if="theDevice.config.otaUpdate?.status === 'available'" class="mt-3">
            <div class="alert alert-info text-left">
                <div>{{ $t('Firmware update is available.') }}</div>
                <div>{{ $t('New version') }}: {{ theDevice.config.otaUpdate.version || '?' }}</div>
                <div v-if="updateUrl">
                    {{ $t('Details') }}:
                    <a :href="updateUrl">{{ updateUrl }}</a>
                </div>
            </div>
        </div>
        <div v-else-if="theDevice.config.otaUpdate?.status === 'notAvailable'" class="mt-3">
            <div class="alert alert-info">
                <div>{{ $t('You have the newest firmware installed.') }}</div>
            </div>
        </div>
        <div class="d-flex mt-1">
            <FormButton :disabled-reason="disabledReason" @click="checkUpdates()" :loading="isCheckingUpdates"
                button-class="btn-default btn-xs btn-block" class="flex-basis-50 pr-1">
                {{ $t('Check available updates') }}
            </FormButton>
            <FormButton :disabled-reason="disabledReason" @click="updateConfirm = true"
                button-class="btn-default btn-xs btn-block" class="flex-basis-50 pl-1">
                {{ $t('Perform firmware update') }}
            </FormButton>
        </div>
        <modal-confirm v-if="updateConfirm"
            class="modal-warning"
            @confirm="performUpdate()"
            :loading="isCheckingUpdates"
            @cancel="updateConfirm = false"
            :header="$t('Are you sure?')">
            <p>{{ $t('This action will result in trying to update the device firmware. It will not be available for a while.') }}</p>
        </modal-confirm>
    </div>
</template>

<script setup>
    import {computed, ref} from "vue";
    import {useDevicesStore} from "@/stores/devices-store";
    import {devicesApi} from "@/api/devices-api";
    import {promiseTimeout, useTimeoutPoll} from "@vueuse/core";
    import {useDisabledActionsReason} from "@/devices/details/disabled-actions-reason";
    import FormButton from "@/common/gui/FormButton.vue";

    const props = defineProps({device: Object});

    const devicesStore = useDevicesStore();

    const {disabledReason, theDevice} = useDisabledActionsReason(props.device);

    const checkCount = ref(0);
    const updateConfirm = ref(false);
    const isCheckingUpdates = ref(false);
    const updateUrl = computed(() => {
        const url = theDevice.value?.config.otaUpdate?.url;
        if (url) {
            return url.startsWith('http') ? url : 'https://updates.supla.org' + url;
        } else {
            return '';
        }
    })

    async function checkUpdates() {
        isCheckingUpdates.value = true;
        await devicesApi.otaCheckUpdates(props.device.id);
        checkCount.value = 0;
        startCheckingUpdates();
    }

    async function performUpdate() {
        isCheckingUpdates.value = true;
        try {
            await devicesApi.otaPerformUpdate(props.device.id);
            await promiseTimeout(5000);
            await devicesStore.fetchDevice(props.device.id);
        } finally {
            isCheckingUpdates.value = false;
            updateConfirm.value = false;
        }
    }

    const {pause: stopCheckingUpdates, resume: startCheckingUpdates} = useTimeoutPoll(async () => {
        const device = await devicesStore.fetchDevice(props.device.id);
        if (['available', 'notAvailable'].includes(device.config?.otaUpdate?.status) || checkCount.value++ > 10) {
            stopCheckingUpdates();
            isCheckingUpdates.value = false;
        }
    }, 3000, {immediate: false})
</script>

<template>
    <div v-if="device.flags.factoryResetSupported" v-tooltip="disabledReason">
        <button class="btn btn-default btn-block btn-wrapped"
            type="button"
            :disabled="!!disabledReason"
            @click="showConfirmDialog = true">
            {{ $t('Factory reset') }}
        </button>
        <modal-confirm v-if="showConfirmDialog"
            class="modal-warning"
            @confirm="factoryResetDevice()"
            :loading="loading"
            @cancel="showConfirmDialog = false"
            :header="$t('Are you sure?')">
            <p>{{ $t('This action will result in erasing all stored information (including Wi-Fi settings, password, mail, server) and will not reconnect automatically.') }}</p>
        </modal-confirm>
    </div>
</template>

<script setup lang="ts">
    import {successNotification} from "@/common/notifier";
    import {computed, ref} from "vue";
    import {devicesApi} from "@/api/devices-api";

    const props = defineProps<{ device: IODevice }>();

    const showConfirmDialog = ref(false);
    const loading = ref(false);

    const disabledReason = computed(() => props.device.connected ? '' : 'Device is disconnected.');

    async function factoryResetDevice() {
        try {
            loading.value = true;
            await devicesApi.factoryReset(props.device.id);
            loading.value = false;
            successNotification(
                'Successful', // i18n
                'The factory reset was successful.' // i18n
            )
        } finally {
            showConfirmDialog.value = false;
            loading.value = false;
        }
    }
</script>

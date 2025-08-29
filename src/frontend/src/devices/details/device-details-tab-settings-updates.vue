<template>
    <div>
        <!-- i18n: ['firmwareUpdatePolicy_ALL_ENABLED', 'firmwareUpdatePolicy_SECURITY_ONLY', 'firmwareUpdatePolicy_DISABLED', 'firmwareUpdatePolicy_FORCED_OFF'] -->
        <div class="form-group" v-if="newConfig.firmwareUpdatePolicy !== undefined">
            <label for="firmwareUpdatePolicy">{{ $t('Firmware update policy') }}</label>
            <SimpleDropdown v-model="newConfig.firmwareUpdatePolicy" :options="['DISABLED', 'SECURITY_ONLY', 'ALL_ENABLED']"
                :disabled="!['DISABLED', 'SECURITY_ONLY', 'ALL_ENABLED'].includes(newConfig.firmwareUpdatePolicy)">
                <template #button="{value}">
                    {{ $t('firmwareUpdatePolicy_' + value) }}
                </template>
                <template #option="{value}">
                    {{ $t('firmwareUpdatePolicy_' + value) }}
                </template>
            </SimpleDropdown>
        </div>
    </div>
</template>

<script setup>
    import {useDeviceSettingsForm} from "@/devices/details/device-details-helpers";
    import SimpleDropdown from "@/common/gui/simple-dropdown.vue";

    const props = defineProps({device: Object});

    const emit = defineEmits(['change']);
    const {newConfig} = useDeviceSettingsForm(props.device.id, emit, (device) => ({
        firmwareUpdatePolicy: device.config.firmwareUpdatePolicy,
    }));
</script>


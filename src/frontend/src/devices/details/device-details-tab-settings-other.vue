<template>
    <div>
        <div class="form-group" v-if="newConfig.automaticTimeSync !== undefined">
            <label class="checkbox2 checkbox2-grey">
                <input type="checkbox" v-model="newConfig.automaticTimeSync">
                {{ $t('Automatic time synchronization') }}
            </label>
        </div>
        <TransitionExpand>
            <div v-if="newConfig.automaticTimeSync === false" class="form-group">
                <DeviceSetTimeButton :device="device" class="mb-2"/>
            </div>
        </TransitionExpand>
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
        <SaveCancelChangesButtons :original="originalConfig" :changes="newConfig" @cancel="cloneConfig()" @save="saveChanges()"/>
    </div>
</template>

<script setup>
    import {useDeviceSettingsForm} from "@/devices/details/device-details-helpers";
    import SaveCancelChangesButtons from "@/devices/details/save-cancel-changes-buttons.vue";
    import DeviceSetTimeButton from "@/devices/details/device-set-time-button.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import SimpleDropdown from "@/common/gui/simple-dropdown.vue";

    const props = defineProps({device: Object});

    const {newConfig, cloneConfig, saveChanges, originalConfig} = useDeviceSettingsForm(props.device.id, (device) => ({
        buttonVolume: device.config.buttonVolume,
        automaticTimeSync: device.config.automaticTimeSync,
        firmwareUpdatePolicy: device.config.firmwareUpdatePolicy,
    }));
</script>


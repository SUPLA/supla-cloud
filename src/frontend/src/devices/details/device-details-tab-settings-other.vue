<template>
    <div>
        <div class="form-group" v-if="newConfig.buttonVolume !== undefined">
            <label>{{ $t('Button volume') }}</label>
            <div class="mt-3 mb-6">
                <VueSlider v-model="newConfig.buttonVolume" :min="0" :max="100" tooltip="always"
                    tooltip-placement="bottom" class="green"/>
            </div>
        </div>
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
        <!-- i18n: ['firmwareUpdatePolicy_ALL_UPDATES', 'firmwareUpdatePolicy_SECURITY_UPDATES', 'firmwareUpdatePolicy_MANUAL_UPDATES', 'firmwareUpdatePolicy_MANUAL_UPDATES'] -->
        <div class="form-group" v-if="newConfig.firmwareUpdatePolicy !== undefined">
            <label for="firmwareUpdatePolicy">{{ $t('Firmware update policy') }}</label>
            <select id="firmwareUpdatePolicy" class="form-control" v-model="newConfig.firmwareUpdatePolicy">
                <option v-for="option in ['ALL_UPDATES', 'SECURITY_UPDATES', 'MANUAL_UPDATES', 'DISABLED']" :key="option"
                    :value="option">
                    {{ $t('firmwareUpdatePolicy_' + option) }}
                </option>
            </select>
        </div>
        <SaveCancelChangesButtons :original="originalConfig" :changes="newConfig" @cancel="cloneConfig()" @save="saveChanges()"/>
    </div>
</template>

<script setup>
    import {useDeviceSettingsForm} from "@/devices/details/device-details-helpers";
    import SaveCancelChangesButtons from "@/devices/details/save-cancel-changes-buttons.vue";
    import VueSlider from 'vue-slider-component';
    import DeviceSetTimeButton from "@/devices/details/device-set-time-button.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    const props = defineProps({device: Object});

    const {newConfig, cloneConfig, saveChanges, originalConfig} = useDeviceSettingsForm(props.device.id, (device) => ({
        buttonVolume: device.config.buttonVolume,
        automaticTimeSync: device.config.automaticTimeSync,
        firmwareUpdatePolicy: device.config.firmwareUpdatePolicy,
    }));
</script>


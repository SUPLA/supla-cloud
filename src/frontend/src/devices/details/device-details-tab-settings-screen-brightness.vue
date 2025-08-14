<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Screen brightness') }}</label>
            <div>
                <label class="checkbox2 checkbox2-grey">
                    <input type="checkbox" v-model="newConfig.auto" @input="onScreenBrightnessAutoChange()">
                    {{ $t('Automatic') }}
                </label>
            </div>
        </div>
        <div class="form-group" v-if="newConfig.auto">
            <label>{{ $t('Brightness adjustment for automatic mode') }}</label>
            <div class="mt-4 mb-6">
                <VueSlider v-model="newConfig.level" :min="-100" :max="100" :interval="10"
                    :process="false" tooltip="always" tooltip-placement="bottom" class="green"
                    :tooltip-formatter="(v) => v === 0 ? $t('default') : ((v > 0 ? '+' + v : v) + '%')"
                    :marks="{0: {label: ''}}">
                    <template #label>
                        <div class="vue-slider-mark-label mark-on-top">
                            <fa icon="circle-half-stroke"/>
                        </div>
                    </template>
                </VueSlider>
            </div>
        </div>
        <div class="form-group" v-else>
            <label>{{ $t('Brightness level') }}</label>
            <div class="mt-3 mb-6">
                <VueSlider v-model="newConfig.level"
                    :data="[1,5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100]"
                    tooltip="always" tooltip-placement="bottom" class="green"
                    :tooltip-formatter="(v) => `${v}%`"/>
            </div>
        </div>
        <SaveCancelChangesButtons :original="device.config.screenBrightness" :changes="newConfig"
            @cancel="cloneConfig()" @save="saveChanges()"/>
    </div>
</template>

<script setup>
    import {ref} from "vue";
    import {deepCopy} from "@/common/utils";
    import {devicesApi} from "@/api/devices-api";
    import {useDevicesStore} from "@/stores/devices-store";
    import VueSlider from 'vue-slider-component';
    import SaveCancelChangesButtons from "@/devices/details/save-cancel-changes-buttons.vue";

    const props = defineProps({device: Object});

    const newConfig = ref({});
    const cloneConfig = () => newConfig.value = deepCopy(props.device.config.screenBrightness);
    cloneConfig();

    const onScreenBrightnessAutoChange = () => newConfig.value.level = newConfig.value.auto ? 50 : 0;

    async function saveChanges() {
        try {
            await devicesApi.update(props.device.id, {
                config: {screenBrightness: newConfig.value},
                configBefore: props.device.config,
            });
        } finally {
            await useDevicesStore().fetchDevice(props.device.id);
            cloneConfig();
        }
    }
</script>


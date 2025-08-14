<template>
    <div>
        <div class="form-group" v-if="device.config.homeScreenContentAvailable && device.config.homeScreenContentAvailable.length">
            <label for="homeScreen">{{ $t('Home screen content') }}</label>
            <!-- i18n:["homeScreenContent_NONE", "homeScreenContent_TEMPERATURE"] -->
            <!-- i18n:["homeScreenContent_TEMPERATURE_AND_HUMIDITY"] -->
            <!-- i18n:["homeScreenContent_TIME", "homeScreenContent_TIME_DATE", "homeScreenContent_TEMPERATURE_TIME"] -->
            <!-- i18n:["homeScreenContent_MAIN_AND_AUX_TEMPERATURE", "homeScreenContent_MODE_OR_TEMPERATURE"] -->
            <select id="homeScreen" class="form-control" v-model="homeScreenContent">
                <option v-for="mode in device.config.homeScreenContentAvailable" :key="mode" :value="mode">
                    {{ $t(`homeScreenContent_${mode}`) }}
                </option>
            </select>
        </div>
        <div class="form-group" v-if="hasOffDelay">
            <label>{{ $t('Automatic front panel turn off') }}</label>
            <div>
                <!-- i18n:["homeScreenContent_offMode_DISABLE", "homeScreenContent_offMode_ALWAYS", "homeScreenContent_offMode_DARK"] -->
                <SimpleDropdown v-model="homeScreenOffMode" :options="['DISABLE', 'ALWAYS', 'DARK']">
                    <template #button="{value}">
                        {{ $t('homeScreenContent_offMode_' + value) }}
                    </template>
                    <template #option="{option}">
                        {{ $t('homeScreenContent_offMode_' + option) }}
                    </template>
                </SimpleDropdown>
            </div>
        </div>
        <transition-expand>
            <div class="form-group mt-2" v-if="homeScreenOffMode !== 'DISABLE' && hasOffDelay">
                <label>{{ $t('Automatic front panel turn off after') }}</label>
                <div class="mt-3 mb-6">
                    <VueSlider v-model="offDelay" tooltip="always"
                        :data="homeScreenOffPossibleDelays" :tooltip-formatter="formatSeconds"
                        tooltip-placement="bottom" class="green"/>
                </div>
            </div>
        </transition-expand>
        <SaveCancelChangesButtons :original="device.config.homeScreen" :changes="newConfig"
            @cancel="readModelFromProps()" @save="saveChanges()"/>
    </div>
</template>

<script setup>
    import 'vue-slider-component/theme/antd.css';
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {prettyMilliseconds} from "@/common/filters";
    import {computed, ref, watch} from "vue";
    import VueSlider from 'vue-slider-component';
    import SimpleDropdown from "@/common/gui/simple-dropdown.vue";
    import SaveCancelChangesButtons from "@/devices/details/save-cancel-changes-buttons.vue";
    import {devicesApi} from "@/api/devices-api";
    import {useDevicesStore} from "@/stores/devices-store";

    const props = defineProps({device: Object});

    const homeScreenContent = ref('NONE');
    const homeScreenOffMode = ref('DISABLE');
    const offDelay = ref(60);

    const hasOffDelay = computed(() => props.device.config.homeScreen?.offDelay !== undefined);
    const hasOffDelayType = computed(() => props.device.config.homeScreen?.offDelayType !== undefined);

    const homeScreenOffPossibleDelays = [
        ...[...Array(30).keys()].map(k => k + 1), // s 1 - 30
        ...[...Array(5).keys()].map(k => k * 5 + 35), // s 35 - 55
        ...[...Array(9).keys()].map(k => k * 30 + 60), // min 1, 1.5, 2, ... 5
        ...[6, 7, 8, 9, 10, 15, 20, 30].map(k => k * 60)
    ];

    function readModelFromProps() {
        homeScreenContent.value = props.device.config.homeScreen.content;
        if (!props.device.config.homeScreen?.offDelay) {
            homeScreenOffMode.value = 'DISABLE';
        } else if (props.device.config.homeScreen?.offDelayType === 'ENABLED_WHEN_DARK') {
            homeScreenOffMode.value = 'DARK';
        } else {
            homeScreenOffMode.value = 'ALWAYS';
        }
        offDelay.value = props.device.config.homeScreen?.offDelay || 60;
    }

    readModelFromProps();
    watch(() => props.device, () => readModelFromProps());

    function formatSeconds(sliderValue) {
        return prettyMilliseconds(+sliderValue * 1000);
    }

    const newConfig = computed(() => {
        const value = {content: homeScreenContent.value};
        if (hasOffDelay.value) {
            if (homeScreenOffMode.value === 'DISABLE') {
                value.offDelay = 0;
            } else {
                value.offDelay = offDelay.value;
            }
        }
        if (hasOffDelayType.value) {
            value.offDelayType = homeScreenOffMode.value === 'DARK' ? 'ENABLED_WHEN_DARK' : 'ALWAYS_ENABLED';
        }
        return value;
    })

    async function saveChanges() {
        try {
            await devicesApi.update(props.device.id, {
                config: {homeScreen: newConfig.value},
                configBefore: props.device.config,
            });
        } finally {
            await useDevicesStore().fetchDevice(props.device.id);
            readModelFromProps();
        }
    }
</script>

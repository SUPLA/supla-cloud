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
                <!-- i18n:["homeScreenContent_offMode_DISABLE", "homeScreenContent_offMode_ALWAYS_ENABLED", "homeScreenContent_offMode_ENABLED_WHEN_DARK"] -->
                <SimpleDropdown v-model="homeScreenOffMode" :options="availableOffDelayTypes">
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
    </div>
</template>

<script setup>
    import 'vue-slider-component/theme/antd.css';
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {prettyMilliseconds} from "@/common/filters";
    import {computed} from "vue";
    import VueSlider from 'vue-slider-component';
    import SimpleDropdown from "@/common/gui/simple-dropdown.vue";
    import {useDeviceSettingsForm} from "@/devices/details/device-details-helpers";
    import {deepCopy} from "@/common/utils";

    const props = defineProps({device: Object});
    const emit = defineEmits(['change']);

    const {newConfig} = useDeviceSettingsForm(props.device.id, emit, () => ({
        homeScreen: deepCopy(props.device.config.homeScreen),
    }));

    const hasOffDelay = computed(() => props.device.config.homeScreen?.offDelay !== undefined);
    const hasOffDelayType = computed(() => props.device.config.homeScreen?.offDelayType !== undefined);

    const homeScreenContent = computed({
        get: () => newConfig.value.homeScreen.content,
        set: (value) => newConfig.value.homeScreen.content = value,
    });

    const availableOffDelayTypes = computed(() => ['DISABLE', 'ALWAYS_ENABLED', hasOffDelayType.value && 'ENABLED_WHEN_DARK'].filter(x => x));

    const homeScreenOffMode = computed({
        get() {
            if (newConfig.value.homeScreen?.offDelay) {
                return newConfig.value.homeScreen.offDelayType || 'ALWAYS_ENABLED';
            } else {
                return 'DISABLE';
            }
        },
        set(mode) {
            if (mode === 'DISABLE') {
                newConfig.value.homeScreen.offDelay = 0;
            } else if (!newConfig.value.homeScreen.offDelay) {
                newConfig.value.homeScreen.offDelay = 60;
            }
            if (hasOffDelayType.value) {
                newConfig.value.homeScreen.offDelayType = mode === 'ENABLED_WHEN_DARK' ? 'ENABLED_WHEN_DARK' : 'ALWAYS_ENABLED';
            }
        },
    });

    const offDelay = computed({
        get: () => newConfig.value.homeScreen.offDelay,
        set: (delay) => newConfig.value.homeScreen.offDelay = delay,
    });

    const homeScreenOffPossibleDelays = [
        ...[...Array(30).keys()].map(k => k + 1), // s 1 - 30
        ...[...Array(5).keys()].map(k => k * 5 + 35), // s 35 - 55
        ...[...Array(9).keys()].map(k => k * 30 + 60), // min 1, 1.5, 2, ... 5
        ...[6, 7, 8, 9, 10, 15, 20, 30].map(k => k * 60)
    ];

    function formatSeconds(sliderValue) {
        return prettyMilliseconds(+sliderValue * 1000);
    }
</script>

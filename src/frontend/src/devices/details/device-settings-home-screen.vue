<template>
    <div>
        <div class="form-group" v-if="config.homeScreenContentAvailable && config.homeScreenContentAvailable.length">
            <label for="homeScreen">{{ $t('Home screen content') }}</label>
            <!-- i18n:["homeScreenContent_NONE", "homeScreenContent_TEMPERATURE"] -->
            <!-- i18n:["homeScreenContent_TEMPERATURE_AND_HUMIDITY"] -->
            <!-- i18n:["homeScreenContent_TIME", "homeScreenContent_TIME_DATE", "homeScreenContent_TEMPERATURE_TIME"] -->
            <!-- i18n:["homeScreenContent_MAIN_AND_AUX_TEMPERATURE"] -->
            <select id="homeScreen" class="form-control" v-model="homeScreenContent" @change="onChange()">
                <option v-for="mode in config.homeScreenContentAvailable" :key="mode" :value="mode">
                    {{ $t(`homeScreenContent_${mode}`) }}
                </option>
            </select>
        </div>
        <div class="form-group" v-if="value.offDelayType !== undefined">
            <label>{{ $t('Automatic front panel turn off') }}</label>
            <div>
                <div class="btn-group">
                    <button type="button" @click="homeScreenOffMode = 'DISABLE'; onChange()"
                        :class="['btn', homeScreenOffMode === 'DISABLE' ? 'btn-green' : 'btn-white']">
                        {{ $t('Disable') }}
                    </button>
                    <button type="button" @click="homeScreenOffMode = 'ALWAYS'; onChange()"
                        :class="['btn', homeScreenOffMode === 'ALWAYS' ? 'btn-green' : 'btn-white']">
                        {{ $t('After the delay') }}
                    </button>
                    <button v-if="value.offDelayType"
                        type="button" @click="homeScreenOffMode = 'DARK'; onChange()"
                        :class="['btn', homeScreenOffMode === 'DARK' ? 'btn-green' : 'btn-white']">
                        {{ $t("After the delay, but only when it's dark") }}
                    </button>
                </div>
            </div>
        </div>
        <transition-expand>
            <div class="form-group mt-2" v-if="homeScreenOffMode !== 'DISABLE' && value.offDelay !== undefined">
                <label>{{ $t('Automatic front panel turn off after') }}</label>
                <div class="mt-3 mb-6">
                    <VueSlider v-model="offDelay" @change="onChange()" tooltip="always"
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
    import {ref, watch} from "vue";
    import VueSlider from 'vue-slider-component';

    const props = defineProps({value: Object, config: Object});
    const emit = defineEmits(['input'])

    const homeScreenContent = ref('NONE');
    const homeScreenOffMode = ref('DISABLE');
    const offDelay = ref(60);

    const homeScreenOffPossibleDelays = [
        ...[...Array(30).keys()].map(k => k + 1), // s 1 - 30
        ...[...Array(5).keys()].map(k => k * 5 + 35), // s 35 - 55
        ...[...Array(9).keys()].map(k => k * 30 + 60), // min 1, 1.5, 2, ... 5
        ...[6, 7, 8, 9, 10, 15, 20, 30].map(k => k * 60)
    ];

    function readModelFromProps() {
        homeScreenContent.value = props.value?.content;
        if (!props.value?.offDelay) {
            homeScreenOffMode.value = 'DISABLE';
        } else if (props.value?.offDelayType === 'ENABLED_WHEN_DARK') {
            homeScreenOffMode.value = 'DARK';
        } else {
            homeScreenOffMode.value = 'ALWAYS';
        }
        offDelay.value = props.value?.offDelay || 60;
    }

    readModelFromProps();
    watch(() => props.value, () => readModelFromProps());

    function formatSeconds(sliderValue) {
        return prettyMilliseconds(+sliderValue * 1000);
    }

    function onChange() {
        const value = {content: homeScreenContent.value};
        if (homeScreenOffMode.value === 'DISABLE') {
            value.offDelay = 0;
        } else {
            value.offDelay = offDelay.value;
        }
        if (props.value.offDelayType) {
            value.offDelayType = homeScreenOffMode.value === 'DARK' ? 'ENABLED_WHEN_DARK' : 'ALWAYS_ENABLED';
        }
        emit('input', value);
    }
</script>

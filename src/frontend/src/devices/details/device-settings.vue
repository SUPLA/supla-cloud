<template>
    <div class="container">
        <pending-changes-page
            :header="$t('Device settings')"
            dont-set-page-title
            @cancel="cancelChanges()"
            @save="saveDeviceSettings()"
            :is-pending="hasPendingChanges">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <div class="form-group" v-if="config.statusLed">
                        <label for="statusLed">{{ $t('LED status') }}</label>
                        <select id="statusLed" class="form-control" v-model="config.statusLed" @change="onChange()">
                            <option value="OFF_WHEN_CONNECTED">{{ $t('statusLed_OFF_WHEN_CONNECTED') }}</option>
                            <option value="ALWAYS_OFF">{{ $t('statusLed_ALWAYS_OFF') }}</option>
                            <option value="ON_WHEN_CONNECTED">{{ $t('statusLed_ON_WHEN_CONNECTED') }}</option>
                        </select>
                    </div>
                    <div class="form-group" v-if="config.screenBrightness !== undefined">
                        <label>{{ $t('Screen brightness') }}</label>
                        <div>
                            <label class="checkbox2 checkbox2-grey">
                                <input type="checkbox" v-model="screenBrightnessAuto" @change="onChange()">
                                {{ $t('Automatic') }}
                            </label>
                        </div>
                        <transition-expand>
                            <div v-if="!screenBrightnessAuto" class="mt-3 mb-5">
                                <VueSlider v-model="config.screenBrightness" :min="0" :max="100" @change="onChange()" tooltip="always"
                                    tooltip-placement="bottom" class="green"/>
                            </div>
                        </transition-expand>
                    </div>
                    <div class="form-group" v-if="config.buttonVolume !== undefined">
                        <label>{{ $t('Button volume') }}</label>
                        <div class="mt-3 mb-5">
                            <VueSlider v-model="config.buttonVolume" :min="0" :max="100" @change="onChange()" tooltip="always"
                                tooltip-placement="bottom" class="green"/>
                        </div>
                    </div>
                    <div
                        v-if="config.screenSaver !== undefined && config.screenSaverModesAvailable && config.screenSaverModesAvailable.length">
                        <div class="form-group">
                            <label for="screenSaverMode">{{ $t('Screen saver mode') }}</label>
                            <!-- i18n:["screenSaverMode_OFF", "screenSaverMode_TEMPERATURE", "screenSaverMode_HUMIDITY"] -->
                            <!-- i18n:["screenSaverMode_TIME", "screenSaverMode_TIME_DATE", "screenSaverMode_TEMPERATURE_TIME"] -->
                            <!-- i18n:["screenSaverMode_MAIN_AND_AUX_TEMPERATURE"] -->
                            <select id="screenSaverMode" class="form-control" v-model="config.screenSaver.mode" @change="onChange()">
                                <option v-for="mode in config.screenSaverModesAvailable" :key="mode" :value="mode">
                                    {{ $t(`screenSaverMode_${mode}`) }}
                                </option>
                            </select>
                        </div>
                        <transition-expand>
                            <div class="form-group" v-if="config.screenSaver.mode !== 'OFF'">
                                <label>{{ $t('Screen saver delay') }}</label>
                                <div class="mt-3 mb-5">
                                    <VueSlider v-model="config.screenSaver.delay" @change="onChange()" tooltip="always"
                                        :data="screenSaverPossibleDelays" :tooltip-formatter="formatMilliseconds"
                                        tooltip-placement="bottom" class="green"/>
                                </div>
                            </div>
                        </transition-expand>
                    </div>
                    <div class="mt-5">
                        <div class="form-group" v-if="config.userInterfaceDisabled !== undefined">
                            <label class="checkbox2 checkbox2-grey">
                                <input type="checkbox" v-model="config.userInterfaceDisabled" @change="onChange()">
                                {{ $t('Disable user interface') }}
                            </label>
                        </div>
                        <div class="form-group" v-if="config.automaticTimeSync !== undefined">
                            <label class="checkbox2 checkbox2-grey">
                                <input type="checkbox" v-model="config.automaticTimeSync" @change="onChange()">
                                {{ $t('Automatic time synchronization') }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </pending-changes-page>
    </div>
</template>

<script>
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import {deepCopy} from "@/common/utils";
    import 'vue-slider-component/theme/antd.css';
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {prettyMilliseconds} from "@/common/filters";

    export default {
        components: {
            TransitionExpand,
            PendingChangesPage,
            VueSlider: () => import('vue-slider-component'),
        },
        props: {
            device: Object,
        },
        data() {
            return {
                hasPendingChanges: false,
                config: undefined,
                screenBrightnessAuto: false,
                screenSaverPossibleDelays: [500, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 4500, // ms
                    ...[...Array(26).keys()].map(k => k * 1000 + 5000), // s 5 - 30
                    ...[...Array(5).keys()].map(k => k * 5000 + 1000 * 35), // s 35 - 55
                    ...[...Array(9).keys()].map(k => k * 30000 + 60000), // min 1, 1.5, 2, ... 5
                ]
            };
        },
        beforeMount() {
            this.cancelChanges();
        },
        methods: {
            onChange() {
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.config = deepCopy(this.device.config);
                if (this.config.screenBrightness === 'auto') {
                    this.screenBrightnessAuto = true;
                    this.config.screenBrightness = 100;
                } else {
                    this.screenBrightnessAuto = false;
                }
                this.hasPendingChanges = false;
            },
            saveDeviceSettings() {
                const config = deepCopy(this.config);
                if (this.screenBrightnessAuto) {
                    config.screenBrightness = 'auto';
                }
                this.$http.put(`iodevices/${this.device.id}`, {config})
                    .then(response => this.device.config = response.body.config)
                    .then(() => this.cancelChanges());
            },
            formatMilliseconds(sliderValue) {
                return prettyMilliseconds(+sliderValue, this);
            },
        },
    }
</script>


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
                    <transition-expand>
                        <ConfigConflictWarning @refresh="replaceConfigWithConflictingConfig()" v-if="conflictingConfig"/>
                    </transition-expand>
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
                            <div v-if="!screenBrightnessAuto" class="mt-3 mb-6">
                                <VueSlider v-model="config.screenBrightness" :min="0" :max="100" @change="onChange()" tooltip="always"
                                    tooltip-placement="bottom" class="green"/>
                            </div>
                        </transition-expand>
                    </div>
                    <div class="form-group" v-if="config.buttonVolume !== undefined">
                        <label>{{ $t('Button volume') }}</label>
                        <div class="mt-3 mb-6">
                            <VueSlider v-model="config.buttonVolume" :min="0" :max="100" @change="onChange()" tooltip="always"
                                tooltip-placement="bottom" class="green"/>
                        </div>
                    </div>
                    <div
                        v-if="config.homeScreen !== undefined && config.homeScreenContentAvailable && config.homeScreenContentAvailable.length">
                        <div class="form-group">
                            <label for="homeScreen">{{ $t('Home screen content') }}</label>
                            <!-- i18n:["homeScreenContent_NONE", "homeScreenContent_TEMPERATURE", "homeScreenContent_HUMIDITY"] -->
                            <!-- i18n:["homeScreenContent_TIME", "homeScreenContent_TIME_DATE", "homeScreenContent_TEMPERATURE_TIME"] -->
                            <!-- i18n:["homeScreenContent_MAIN_AND_AUX_TEMPERATURE"] -->
                            <select id="homeScreen" class="form-control" v-model="config.homeScreen.content" @change="onChange()">
                                <option v-for="mode in config.homeScreenContentAvailable" :key="mode" :value="mode">
                                    {{ $t(`homeScreenContent_${mode}`) }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="checkbox2 checkbox2-grey">
                                <input type="checkbox" v-model="homeScreenOff" @change="onChange()">
                                {{ $t('Turn off the screen when inactive') }}
                            </label>
                        </div>
                        <transition-expand>
                            <div class="form-group mt-2" v-if="homeScreenOff">
                                <label>{{ $t('Turn off after') }}</label>
                                <div class="mt-3 mb-6">
                                    <VueSlider v-model="config.homeScreen.offDelay" @change="onChange()" tooltip="always"
                                        :data="homeScreenOffPossibleDelays" :tooltip-formatter="formatSeconds"
                                        tooltip-placement="bottom" class="green"/>
                                </div>
                            </div>
                        </transition-expand>
                    </div>
                    <div class="form-group" v-if="config.userInterface">
                        <label>{{ $t('User interface') }}</label>
                        <div>
                            <div class="btn-group">
                                <button type="button" @click="config.userInterface.disabled = false; onChange()"
                                    :class="['btn', config.userInterface.disabled === false ? 'btn-green' : 'btn-white']">
                                    {{ $t('Unlocked') }}
                                </button>
                                <button type="button" @click="config.userInterface.disabled = true; onChange()"
                                    :class="['btn', config.userInterface.disabled === true ? 'btn-green' : 'btn-white']">
                                    {{ $t('Locked') }}
                                </button>
                                <button type="button" @click="config.userInterface.disabled = 'partial'; onChange()"
                                    :class="['btn', config.userInterface.disabled === 'partial' ? 'btn-green' : 'btn-white']">
                                    {{ $t('Temperature adjustment only') }}
                                </button>
                            </div>
                        </div>
                        <div class="pl-4 mt-3" v-if="config.userInterface.disabled === 'partial'">
                            <div class="form-group">
                                <label>{{ $t('Minimum temperature that can be set from the device') }}</label>
                                <input type="number" class="form-control" step="0.1"
                                    @change="onChange()"
                                    v-model="config.userInterface.minAllowedTemperatureSetpointFromLocalUI"
                                    :max="config.userInterface.maxAllowedTemperatureSetpointFromLocalUI || maxUiTemperature"
                                    :min="minUiTemperature" :placeholder="minUiTemperature">
                            </div>
                            <div class="form-group">
                                <label>{{ $t('Maximum temperature that can be set from the device') }}</label>
                                <input type="number" class="form-control" step="0.1"
                                    @change="onChange()"
                                    v-model="config.userInterface.maxAllowedTemperatureSetpointFromLocalUI"
                                    :min="config.userInterface.minAllowedTemperatureSetpointFromLocalUI || minUiTemperature"
                                    :max="maxUiTemperature" :placeholder="maxUiTemperature">
                            </div>
                        </div>
                    </div>
                    <div class="mt-5">
                        <div class="form-group" v-if="config.automaticTimeSync !== undefined">
                            <label class="checkbox2 checkbox2-grey">
                                <input type="checkbox" v-model="config.automaticTimeSync" @change="onChange()">
                                {{ $t('Automatic time synchronization') }}
                            </label>
                            <div class="d-flex">
                                <div v-if="!config.automaticTimeSync"
                                    v-tooltip.bottom="hasPendingChanges ? $t('Save or discard configuration changes first.') : ''">
                                    <a :class="{disabled: hasPendingChanges}" @click="setDeviceTime()">{{ $t('Set the device time') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </pending-changes-page>
        <modal-confirm v-if="deviceTime"
            :header="$t('Set the device time')"
            :loading="settingDeviceTime"
            @cancel="deviceTime = undefined"
            @confirm="setDeviceTime(deviceTime)">
            <div class="form-group">
                <input type="datetime-local" v-model="deviceTime" class="form-control">
            </div>
        </modal-confirm>
    </div>
</template>

<script>
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import {deepCopy} from "@/common/utils";
    import 'vue-slider-component/theme/antd.css';
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {prettyMilliseconds} from "@/common/filters";
    import ConfigConflictWarning from "@/channels/config-conflict-warning.vue";
    import {DateTime} from "luxon";
    import {successNotification} from "@/common/notifier";

    export default {
        components: {
            ConfigConflictWarning,
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
                homeScreenOff: false,
                homeScreenOffPossibleDelays: [
                    ...[...Array(30).keys()].map(k => k + 1), // s 1 - 30
                    ...[...Array(5).keys()].map(k => k * 5 + 35), // s 35 - 55
                    ...[...Array(9).keys()].map(k => k * 30 + 60), // min 1, 1.5, 2, ... 5
                    ...[6, 7, 8, 9, 10, 15, 20, 30].map(k => k * 60)
                ],
                conflictingConfig: false,
                deviceTime: undefined,
                settingDeviceTime: false,
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
                const previousBrightness = Number.isFinite(this.config?.screenBrightness) ? this.config.screenBrightness : 100;
                this.config = deepCopy(this.device.config);
                if (this.config.screenBrightness === 'auto') {
                    this.screenBrightnessAuto = true;
                    this.config.screenBrightness = previousBrightness;
                } else {
                    this.screenBrightnessAuto = false;
                }
                if (this.config.homeScreen) {
                    this.homeScreenOff = !!this.config.homeScreen?.offDelay;
                    if (!this.homeScreenOff) {
                        this.config.homeScreen.offDelay = 60;
                    }
                }
                this.hasPendingChanges = false;
            },
            saveDeviceSettings() {
                const config = deepCopy(this.config);
                if (this.screenBrightnessAuto) {
                    config.screenBrightness = 'auto';
                }
                if (config.homeScreen && !this.homeScreenOff) {
                    config.homeScreen.offDelay = 0;
                }
                if (config.userInterface && config.userInterface.disabled !== 'partial') {
                    config.userInterface = {disabled: config.userInterface.disabled};
                }
                this.$http.put(`iodevices/${this.device.id}`, {config, configBefore: this.device.configBefore}, {skipErrorHandler: [409]})
                    .then(response => {
                        this.device.config = response.body.config;
                        this.device.configBefore = response.body.configBefore;
                    })
                    .then(() => this.cancelChanges())
                    .catch(response => {
                        if (response.status === 409) {
                            this.conflictingConfig = response.body.details.config;
                        }
                    });
            },
            formatSeconds(sliderValue) {
                return prettyMilliseconds(+sliderValue * 1000, this);
            },
            replaceConfigWithConflictingConfig() {
                this.device.config = this.conflictingConfig;
                this.device.configBefore = deepCopy(this.device.config);
                this.conflictingConfig = false;
                this.cancelChanges();
            },
            setDeviceTime(deviceTime) {
                if (deviceTime) {
                    const time = DateTime.fromISO(deviceTime).startOf('second').toISO({suppressMilliseconds: true});
                    this.settingDeviceTime = true;
                    this.$http.patch(`iodevices/${this.device.id}`, {action: 'setTime', time}).then(() => {
                        this.deviceTime = undefined;
                        successNotification(this.$t('Success'), this.$t('Device time has been set.'));
                    }).finally(() => this.settingDeviceTime = false);
                } else {
                    this.deviceTime = DateTime.now()
                        .startOf('second')
                        .toISO({includeOffset: false, suppressMilliseconds: true});
                }
            },
        },
        computed: {
            minUiTemperature() {
                return this.config.userInterfaceConstraints?.minAllowedTemperatureSetpoint;
            },
            maxUiTemperature() {
                return this.config.userInterfaceConstraints?.maxAllowedTemperatureSetpoint
            },
        }
    }
</script>


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
                    <div class="form-group with-border-bottom" v-if="config.statusLed">
                        <label for="statusLed">{{ $t('Status LED') }}</label>
                        <select id="statusLed" class="form-control" v-model="config.statusLed" @change="onChange()">
                            <option value="OFF_WHEN_CONNECTED">{{ $t('statusLed_OFF_WHEN_CONNECTED') }}</option>
                            <option value="ALWAYS_OFF">{{ $t('statusLed_ALWAYS_OFF') }}</option>
                            <option value="ON_WHEN_CONNECTED">{{ $t('statusLed_ON_WHEN_CONNECTED') }}</option>
                        </select>
                    </div>
                    <div v-if="config.screenBrightness !== undefined" class="with-border-bottom">
                        <div class="form-group">
                            <label>{{ $t('Screen brightness') }}</label>
                            <div>
                                <label class="checkbox2 checkbox2-grey">
                                    <input type="checkbox" v-model="config.screenBrightness.auto" @change="onScreenBrightnessAutoChange()">
                                    {{ $t('Automatic') }}
                                </label>
                            </div>
                        </div>
                        <div class="form-group" v-if="config.screenBrightness.auto">
                            <label>{{ $t('Brightness adjustment for automatic mode') }}</label>
                            <div class="mt-4 mb-6">
                                <VueSlider v-model="config.screenBrightness.level" :min="-100" :max="100" :interval="10"
                                    @change="onChange()"
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
                                <VueSlider v-model="config.screenBrightness.level" @change="onChange()"
                                    :min="0" :max="100" :interval="5"
                                    tooltip="always" tooltip-placement="bottom" class="green"
                                    :tooltip-formatter="(v) => (v === 0 ? 1 : v) + '%'"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-group with-border-bottom" v-if="config.buttonVolume !== undefined">
                        <label>{{ $t('Button volume') }}</label>
                        <div class="mt-3 mb-6">
                            <VueSlider v-model="config.buttonVolume" :min="0" :max="100" @change="onChange()" tooltip="always"
                                tooltip-placement="bottom" class="green"/>
                        </div>
                    </div>
                    <div class="form-group with-border-bottom" v-if="config.homeScreen !== undefined">
                        <DeviceSettingsHomeScreen v-model="config.homeScreen" :config="config" @input="onChange()"/>
                    </div>
                    <div class="form-group with-border-bottom" v-if="config.userInterface">
                        <label>{{ $t('Device interface') }}</label>
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button"
                                data-toggle="dropdown">
                                {{ $t('Lock type') }}:
                                {{ $t(`localUILock_${localUILockMode}`) }}
                                <span class="caret"></span>
                            </button>
                            <!-- i18n:['localUILock_UNLOCKED', 'localUILock_FULL', 'localUILock_TEMPERATURE'] -->
                            <ul class="dropdown-menu">
                                <li v-for="type in localUILockingCapabilities" :key="type">
                                    <a @click="localUILockMode = type"
                                        v-show="type !== localUILockMode">
                                        {{ $t(`localUILock_${type}`) }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-3" v-if="config.userInterface.disabled === 'partial'">
                            <label>{{ $t('Temperatures that can be set from local UI') }}</label>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <div>{{ $t('Minimum') }}</div>
                                        <input type="number" class="form-control" step="0.1"
                                            @change="onChange()"
                                            v-model="config.userInterface.minAllowedTemperatureSetpointFromLocalUI"
                                            :max="config.userInterface.maxAllowedTemperatureSetpointFromLocalUI || maxUiTemperature"
                                            :min="minUiTemperature" :placeholder="minUiTemperature">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <div>{{ $t('Maximum') }}</div>
                                        <input type="number" class="form-control" step="0.1"
                                            @change="onChange()"
                                            v-model="config.userInterface.maxAllowedTemperatureSetpointFromLocalUI"
                                            :min="config.userInterface.minAllowedTemperatureSetpointFromLocalUI || minUiTemperature"
                                            :max="maxUiTemperature" :placeholder="maxUiTemperature">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group with-border-bottom" v-if="config.automaticTimeSync !== undefined">
                        <label class="checkbox2 checkbox2-grey">
                            <input type="checkbox" v-model="config.automaticTimeSync" @change="onChange()">
                            {{ $t('Automatic time synchronization') }}
                        </label>
                    </div>
                    <div class="form-group with-border-bottom" v-if="powerStatusLedBoolean !== undefined">
                        <label class="checkbox2 checkbox2-grey">
                            <input type="checkbox" v-model="powerStatusLedBoolean" @change="onChange()">
                            {{ $t('Enable power status LED') }}
                        </label>
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
    import ConfigConflictWarning from "@/channels/config-conflict-warning.vue";
    import DeviceSettingsHomeScreen from "@/devices/details/device-settings-home-screen.vue";

    export default {
        components: {
            DeviceSettingsHomeScreen,
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
                conflictingConfig: false,
                localUILockingCapabilities: ['UNLOCKED', 'FULL', 'TEMPERATURE'],
            };
        },
        beforeMount() {
            this.cancelChanges();
        },
        methods: {
            onScreenBrightnessAutoChange() {
                if (this.config.screenBrightness.auto) {
                    this.config.screenBrightness.level = 0;
                } else {
                    this.config.screenBrightness.level = 50;
                }
                this.onChange();
            },
            onChange() {
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.config = deepCopy(this.device.config);
                this.hasPendingChanges = false;
            },
            saveDeviceSettings() {
                const config = deepCopy(this.config);
                if (config.screenBrightness) {
                    if (!config.screenBrightness.auto && !this.config.screenBrightness.level) {
                        config.screenBrightness.level = 1;
                    }
                }
                if (config.userInterface && config.userInterface.disabled !== 'partial') {
                    config.userInterface = {disabled: config.userInterface.disabled};
                }
                this.$http.put(`iodevices/${this.device.id}?safe=true`, {
                    config,
                    configBefore: this.device.configBefore
                }, {skipErrorHandler: [409]})
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
            replaceConfigWithConflictingConfig() {
                this.device.config = this.conflictingConfig;
                this.device.configBefore = deepCopy(this.device.config);
                this.conflictingConfig = false;
                this.cancelChanges();
            },
        },
        computed: {
            minUiTemperature() {
                return this.config.userInterfaceConstraints?.minAllowedTemperatureSetpoint;
            },
            maxUiTemperature() {
                return this.config.userInterfaceConstraints?.maxAllowedTemperatureSetpoint
            },
            powerStatusLedBoolean: {
                get() {
                    return this.config.powerStatusLed === undefined ? undefined : this.config.powerStatusLed === 'ENABLED';
                },
                set(value) {
                    this.config.powerStatusLed = value ? 'ENABLED' : 'DISABLED';
                }
            },
            localUILockMode: {
                get() {
                    if (this.config.userInterface.disabled === 'partial') {
                        return 'TEMPERATURE';
                    }
                    return this.config.userInterface.disabled ? 'FULL' : 'UNLOCKED';
                },
                set(mode) {
                    if (mode === 'FULL') {
                        this.config.userInterface.disabled = true;
                    } else if (mode === 'TEMPERATURE') {
                        this.config.userInterface.disabled = 'partial';
                    } else {
                        this.config.userInterface.disabled = false;
                    }
                    this.onChange();
                }
            },
        }
    }
</script>

<style lang="scss">
    @import "../../styles/variables";

    .vue-slider-mark-label.mark-on-top {
        top: auto !important;
        bottom: 100%;
        margin-bottom: 10px;
    }

    .with-border-bottom {
        padding-bottom: 1.5em;
        margin-bottom: 1.5em;
        border-bottom: 1px solid $supla-grey-light;
        &:last-child {
            border: 0;
        }
    }
</style>

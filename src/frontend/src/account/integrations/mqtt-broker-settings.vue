<template>
    <loading-cover :loading="settings === undefined">
        <div class="container"
            v-if="settings !== undefined">

            <div class="clearfix left-right-header">
                <div>
                    <h5>{{ $t('Your account and MQTT Broker connection.') }}</h5>
                    <div class="form-group">
                        <div class="btn-group">
                            <a class="btn btn-white"
                                target="_blank"
                                href="https://mqtt.org/">mqtt.org</a>
                            <a class="btn btn-white"
                                target="_blank"
                                href="https://en.wikipedia.org/wiki/MQTT">MQTT (Wikipedia)</a>
                        </div>
                    </div>
                </div>
                <div>
                    <a class="btn btn-green btn-lg"
                        v-if="!settings.userEnabled"
                        @click="enableMqttBrokerSupport()">
                        <button-loading-dots v-if="fetching"></button-loading-dots>
                        <span v-else>
                            <span class="pe-7s-power"></span>
                            {{ $t('Enable') }}
                        </span>
                    </a>
                    <a class="btn btn-orange btn-lg"
                        v-else
                        @click="disableMqttBrokerSupport()">
                        <button-loading-dots v-if="fetching"></button-loading-dots>
                        <span v-else>
                            <span class="pe-7s-power"></span>
                            {{ $t('Disable') }}
                        </span>
                    </a>
                </div>
            </div>

            <div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 mqtt-settings">
                <div class="panel panel-green"
                    v-if="settings.userEnabled">
                    <div class="panel-body">
                        <div v-if="settings.host">
                            <input type="text"
                                v-model="settings.host"
                                readonly>
                            <label>{{ $t('Host') }}</label>
                            <input type="text"
                                v-model="settings.protocol"
                                readonly>
                            <label>{{ $t('Protocol') }}</label>
                            <input type="text"
                                v-model="settings.port"
                                readonly>
                            <label>{{ $t('Port') }}</label>
                            <input type="text"
                                :value="settings.tls ? $t('Enabled') : $t('Disabled')"
                                readonly>
                            <label>{{ $t('TLS') }}</label>
                        </div>
                        <div v-if="settings.integratedAuth">
                            <div class="form-group">
                                <input type="text"
                                    v-model="settings.username"
                                    readonly>
                                <label>{{ $t('MQTT Broker Username') }}</label>
                            </div>
                            <div>
                                <div class="alert alert-warning"
                                    v-if="generatedPassword">
                                    <h4>{{ $t('MQTT Broker password') }}</h4>
                                    <div class="flex-left-full-width form-group">
                                        <pre><code>{{ generatedPassword }}</code></pre>
                                        <copy-button :text="generatedPassword"></copy-button>
                                    </div>
                                    <p>{{ $t('The MQTT Broker password is visible now only. Make sure to save it in a safe place. You will not be able to see it again.') }}</p>
                                </div>
                                <a @click="generatingPassword = true"
                                    class="btn btn-white">{{ $t('Generate new password') }}</a>
                            </div>
                            <label>{{ $t('MQTT Broker password') }}</label>
                        </div>
                        <div v-else-if="settings.hasLocalCredentials"
                            class="alert alert-info">
                            {{ $t('In order to get the MQTT Broker credentials, concact the administator of this SUPLA Cloud instance.') }}
                        </div>
                        <div v-else
                            class="alert alert-warning">
                            {{ $t('MQTT Broker username and password is not configured in this SUPLA Cloud instance.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <modal-confirm @confirm="generateMqttPassword()"
            class="modal-warning"
            v-if="generatingPassword"
            @cancel="generatingPassword = false"
            :header="$t('MQTT Broker password')">
            {{ $t('The new password will replace the existing one. Some of the current integrations may not work anymore. Proceed?') }}
        </modal-confirm>
    </loading-cover>
</template>

<script>
    import {infoNotification, successNotification} from "../../common/notifier";
    import CopyButton from "../../common/copy-button.vue";

    export default {
        components: {CopyButton},
        data() {
            return {
                settings: undefined,
                publishEnabled: false,
                generatingPassword: false,
                generatedPassword: undefined,
                fetching: false,
            };
        },
        mounted() {
            this.fetchSettings();
        },
        methods: {
            fetchSettings() {
                this.fetching = true;
                this.$http.get('settings/mqtt-broker').then(({body: settings}) => {
                    this.settings = settings;
                    this.fetching = false;
                });
            },
            generateMqttPassword() {
                this.fetching = true;
                this.$http.patch(`users/current`, {action: 'change:mqttBrokerPassword', password: this.password})
                    .then((response) => this.generatedPassword = response.headers.get('SUPLA-MQTT-Password'))
                    .then(() => {
                        this.generatingPassword = false;
                        successNotification(this.$t('Successful'), this.$t('MQTT Broker password has been changed.'));
                        return this.fetchSettings();
                    });
            },
            enableMqttBrokerSupport() {
                this.fetching = true;
                return this.$http.patch(`users/current`, {action: 'change:mqttBrokerEnabled', enabled: true})
                    .then((response) => this.generatedPassword = response.headers.get('SUPLA-MQTT-Password'))
                    .then(() => successNotification(this.$t('Successful'), this.$t('Integration with MQTT Broker has been enabled.')))
                    .finally(this.fetchSettings);
            },
            disableMqttBrokerSupport() {
                this.fetching = true;
                return this.$http.patch(`users/current`, {action: 'change:mqttBrokerEnabled', enabled: false})
                    .then(() => infoNotification(this.$t('Successful'), this.$t('Integration with MQTT Broker has been disabled.')))
                    .finally(this.fetchSettings);
            },
        }
    };
</script>

<style lang="scss">
    @import '../../styles/mixins';
    @import '../../styles/variables';

    .mqtt-state-header {
        font-size: 2em;
        span {
            font-weight: bold;
        }
    }

    .mqtt-settings {
        input[type="text"] {
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(0, 2, 4, .25);
            width: 100%;
            line-height: 36px;
            padding: 0 5px;
            margin-top: 8px;
            font-size: 16px;
        }
        label {
            font-size: 13px;
            font-weight: 400;
            color: rgba(0, 2, 4, .6);
            line-height: 26px;
        }
    }
</style>


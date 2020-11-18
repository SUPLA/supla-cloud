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
                        @click="turnMqttBrokerOn()">
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
                                v-model="settings.tls ? $t('Enabled') : $t('Disabled')"
                                readonly>
                            <label>{{ $t('TLS') }}</label>
                        </div>
                        <div v-if="settings.integratedAuth">
                            <div class="form-group">


                                <input type="text"
                                    v-model="$user.username"
                                    readonly>
                                <label>{{ $t('MQTT Broker Username') }}</label>
                            </div>
                            <div>
                                <a @click="enteringPassword = true"
                                    class="btn btn-white">{{ $t('Change') }}</a>
                            </div>
                            <label>{{ $t('MQTT Broker Password') }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <modal class="square-modal-chooser"
            cancellable="true"
            @cancel="enteringPassword = false"
            @confirm="changeMqttPassword()"
            v-if="enteringPassword"
            :header="$t('MQTT Broker Password')">
            <p>{{ $t('Set a password you will use to authenticate for your account in the MQTT Broker.') }}</p>
            <p>{{ $t('This must be different than the password you use to authenticate in the SUPLA Cloud.') }}</p>
            <p class="text-warning"
                v-if="settings.hasPassword">
                {{ $t('The new password will replace the existing. Some of current integrations may not work anymore.') }}
            </p>
            <span class="input-group">
                <input :type="this.passwordHidden ? 'password' : 'text'"
                    class="form-control"
                    v-model="password">
                <span class="input-group-btn">

                    <a class="btn btn-white"
                        @click="generatePassword(password.length || 5)">{{$t('GENERATE')}}</a>
                    <a :class="'btn btn-' + (passwordHidden ? 'green' : 'white')"
                        @click="passwordHidden = !passwordHidden">
                        &lowast;
                    </a>
                </span>
            </span>
            <span class="help-block">
                {{ $t('Password requirements: minimum length of {minLength} characters, both uppercase and lowercase letters, at least one number and a special character.', {minLength: 10}) }}
            </span>
        </modal>
    </loading-cover>
</template>

<script>
    import {infoNotification, successNotification} from "../common/notifier";
    import {generatePassword} from "../common/utils";

    export default {
        data() {
            return {
                settings: undefined,
                publishEnabled: false,
                enteringPassword: false,
                password: '',
                passwordHidden: true,
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
            changeMqttPassword() {
                this.fetching = true;
                this.$http.patch(`users/current`, {action: 'change:mqttBrokerPassword', password: this.password})
                    .then(() => {
                        if (!this.settings.userEnabled) {
                            return this.enableMqttBrokerSupport();
                        } else {
                            successNotification(this.$t('Successful'), this.$t('MQTT Broker password has been changed.'));
                            return this.fetchSettings();
                        }
                    })
                    .then(() => {
                        this.password = '';
                        this.passwordHidden = true;
                        this.enteringPassword = false;
                    });
            },
            turnMqttBrokerOn() {
                if (this.settings.hasPassword || !this.settings.integratedAuth) {
                    this.enableMqttBrokerSupport();
                } else {
                    this.enteringPassword = true;
                }
            },
            enableMqttBrokerSupport() {
                this.fetching = true;
                return this.$http.patch(`users/current`, {action: 'change:mqttBrokerEnabled', enabled: true})
                    .then(() => {
                        successNotification(this.$t('Successful'), this.$t('Integration with MQTT Broker has been enabled.'));
                        this.fetchSettings();
                    });
            },
            disableMqttBrokerSupport() {
                this.fetching = true;
                return this.$http.patch(`users/current`, {action: 'change:mqttBrokerEnabled', enabled: false})
                    .then(() => {
                        infoNotification(this.$t('Successful'), this.$t('Integration with MQTT Broker has been disabled.'));
                        this.fetchSettings();
                    });
            },
            generatePassword() {
                this.password = generatePassword(16, true);
                this.passwordHidden = false;
            },
        }
    };
</script>

<style lang="scss">
    @import '../styles/mixins';
    @import '../styles/variables';

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


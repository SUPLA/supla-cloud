<template>
    <loading-cover :loading="settings === undefined">
        <div class="container"
            v-if="settings !== undefined">
            <h5>{{ $t('MQTT Broker allows you to publish your channels state to supported consumers.') }}</h5>
            <div class="form-group text-center mqtt-state-header">
                <h4>{{ $t('Sharing the state of your channels to the MQTT Broker:') }}</h4>
                <span v-if="settings.userEnabled"
                    class="text-success">{{ $t('ENABLED') }}</span>
                <span v-else>{{ $t('DISABLED') }}</span>
            </div>
            <div v-if="settings.userEnabled"
                class="text-center">
                <dl v-if="settings.host">
                    <dt>{{ $t('Host') }}</dt>
                    <dd>{{ settings.host }}</dd>
                </dl>
                <dl v-if="settings.protocol">
                    <dt>{{ $t('Protocol') }}</dt>
                    <dd>{{ settings.protocol }}</dd>
                </dl>
                <dl v-if="settings.port">
                    <dt>{{ $t('Port') }}</dt>
                    <dd>{{ settings.port }}</dd>
                </dl>
                <dl v-if="settings.tls">
                    <dt>{{ $t('TLS') }}</dt>
                    <dd>{{ settings.tls ? $t('Enabled') : $t('Disabled') }}</dd>
                </dl>
                <dl v-if="settings.integratedAuth">
                    <dt>{{ $t('MQTT Broker Password') }}</dt>
                    <dd><a @click="enteringPassword = true">{{ $t('Change') }}</a></dd>
                </dl>
            </div>
            <div class="form-group text-center">
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
                {{ $t('Password requirements: minimum length of 10 characters, both uppercase and lowercase letters, at least one number and a special character.') }}
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
                        this.enteringPassword = false
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
    .mqtt-state-header {
        padding-top: 1em;
        span {
            font-weight: bold;
        }
    }
</style>


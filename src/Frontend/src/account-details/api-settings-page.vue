<template>
    <div :class="'container api-settings-page ' + (animationFinished ? 'animation-finished' : '')"
        v-title="'RESTful API'">
        <animated-svg id="api-settings-bg"
            :file="'assets/img/api-settings-account-bg.svg' | withBaseUrl"></animated-svg>
        <div class="terminal">
            {{ $t('Welcome Software Developer! RESTfulAPI has been designed having your needs in mind! With this interface you can integrate your own IT solutions with SUPLA.' )}}
        </div>
        <div class="settings"
            v-if="settings">
            <a :class="'pull-right enable-disable-button btn ' + (settings.user.enabled ? 'btn-default' : 'btn-danger')"
                @click="toggleEnabled()">
                <button-loading-dots v-if="loading"></button-loading-dots>
                <span v-else>
                    <strong class="clearfix">{{ $t(settings.user.enabled ? 'ENABLED' : 'DISABLED') }}</strong>
                    {{ $t('CLICK TO ' + (settings.user.enabled ? 'DISABLE' : 'ENABLE')) }}
                </span>
            </a>
            <h2>RESTful API</h2>
            <p>{{ $t('OAuth 2.0 Authentication') }}</p>
            <div class="inputs form-group">
                <api-setting-copy-input label="Server"
                    v-model="settings.server"></api-setting-copy-input>
                <api-setting-copy-input label="Token URL"
                    v-model="settings.tokenUrl"></api-setting-copy-input>
                <api-setting-copy-input label="ClientID"
                    v-model="settings.client.publicId"></api-setting-copy-input>
                <api-setting-copy-input label="Secret"
                    v-model="settings.client.secret"></api-setting-copy-input>
                <api-setting-copy-input label="GrantType"
                    v-model="settings.client.grantType"></api-setting-copy-input>
                <api-setting-copy-input label="Username"
                    v-model="settings.user.username"></api-setting-copy-input>
                <div v-if="password">
                    <div class="input-group password">
                        <password-display :password="password"
                            class="form-control"></password-display>
                        <div class="input-group-btn">
                            <a class="btn pull-right btn-default"
                                v-clipboard:copy="password">
                                {{ $t('copy') }}
                            </a>
                        </div>
                    </div>
                    <label>Password</label>
                </div>
            </div>
            <div class="btn-toolbar"
                v-if="settings.user.enabled">
                <a class="btn btn-default"
                    @click="generatingPassword = true">
                    Generate password
                </a>
                <a class="btn btn-default"
                    href="/api/docs.html">
                    API Documentation
                </a>
                <a class="btn pull-right btn-default"
                    v-clipboard:copy="allSettings">
                    Copy all
                </a>
            </div>
            <modal-confirm @confirm="generatePassword()"
                @cancel="generatingPassword = false"
                v-if="generatingPassword"
                :loading="loading"
                :header="$t('Are you sure?')">
                {{ $t('If you create new password, reconfiguration of all the connected 3rd party applications will be required.') }}
            </modal-confirm>

        </div>
    </div>
</template>

<script type="text/babel">
    import AnimatedSvg from "./animated-svg";
    import Vue from "vue";
    import VueClipboard from 'vue-clipboard2';
    import ApiSettingCopyInput from "./api-setting-copy-input";
    import PasswordDisplay from "../common/gui/password-display";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";

    Vue.use(VueClipboard);

    export default {
        components: {PasswordDisplay, ApiSettingCopyInput, AnimatedSvg, ButtonLoadingDots},
        data() {
            return {
                settings: undefined,
                generatingPassword: false,
                password: undefined,
                animationFinished: false,
                loading: true,
            };
        },
        mounted() {
            setTimeout(() => this.animationFinished = true, 2000);
            this.$http.get('users/current/api-settings').then(response => {
                this.settings = response.body;
                this.loading = false;
            });
        },
        methods: {
            generatePassword() {
                this.loading = true;
                this.$http.patch('users/current/api-settings', {action: 'generatePassword'}).then(response => {
                    this.password = response.body.password;
                    this.generatingPassword = false;
                }).finally(() => this.loading = false);
            },
            toggleEnabled() {
                this.loading = true;
                this.$http.patch('users/current/api-settings', {action: 'toggleEnabled'}).then(response => {
                    this.settings.user.enabled = response.body.enabled;
                }).finally(() => this.loading = false);
            }
        },
        computed: {
            allSettings() {
                return JSON.stringify(this.settings && {
                    server: this.settings.server,
                    tokenUrl: this.settings.tokenUrl,
                    publicId: this.settings.client.publicId,
                    secret: this.settings.client.secret,
                    grantType: this.settings.client.grantType,
                    username: this.settings.user.username,
                    password: this.password,
                });
            }
        }
    };
</script>

<style lang="scss">
    @import '../styles/mixins';
    @import '../styles/variables';

    ._api_settings {
        background-color: $supla-green;
    }

    .api-settings-page {
        $width: 980px;
        #api-settings-bg {
            display: none;
        }
        .terminal {
            color: $supla-green;
            background: $supla-black;
            font-family: Courier New, Courier, Lucida Sans Typewriter, Lucida Typewriter, monospace;
            font-size: 16px;
            padding: 10px;
        }
        .settings {
            color: $supla-white;
            .inputs {
                .form-control {
                    background: transparent;
                    border: 0;
                    padding: 0;
                    color: $supla-white;
                    box-shadow: none;
                }
            }
            label {
                opacity: .9;
                font-weight: normal;
            }
            .input-group {
                border-bottom: 1px solid $supla-white;
                padding-bottom: 3px;
                &.password .form-control {
                    padding-top: 7px;
                }
                .btn {
                    border-radius: 5px;
                    text-transform: uppercase;
                }
            }
            .password-display {
                a { color: $supla-white;}
                .password {
                    text-shadow: 0 0 7px rgba($supla-white, 90%);
                }
            }
        }
        @include on-and-up($width) {
            width: $width;
            margin: 0 auto;
            position: relative;
            #api-settings-bg {
                display: block;
            }
            .terminal {
                opacity: 0;
                transition: opacity .5s;
                position: absolute;
                top: 163px;
                left: 549px;
                display: block;
                width: 395px;
                height: 356px;
            }
            .settings {
                transition: opacity .5s;
                transition-delay: .3s;
                opacity: 0;
                position: absolute;
                top: 45px;
                left: 45px;
                width: 481px;
                .enable-disable-button {
                    margin-top: 15px;
                }
                .inputs {
                    label {
                        margin-top: 5px;
                    }
                }
            }
            &.animation-finished {
                .settings {
                    opacity: 1;
                }
                .terminal {
                    opacity: 1;
                }
            }
        }
    }
</style>

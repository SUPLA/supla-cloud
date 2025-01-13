<template>
    <div class="centered-form login-form">
        <div class="logo">
            <img src="../assets/img/logo.svg"
                alt="SUPLA">
        </div>
        <slot name="aboveForm"></slot>
        <div class="form-group form-group-lg">
            <span class="input-group">
                <span class="input-group-addon">
                    <span class="pe-7s-user"></span>
                </span>
                <input type="email"
                    required
                    autocorrect="off"
                    autocapitalize="none"
                    v-focus="true"
                    :placeholder="$t('Your email')"
                    v-model="username"
                    name="_username"
                    class="form-control">
            </span>
        </div>
        <div class="form-group form-group-lg login-password">
            <span class="input-group">
                <span class="input-group-addon">
                    <span class="pe-7s-lock"></span>
                </span>
                <input type="password"
                    :placeholder="$t('Password')"
                    name="_password"
                    v-model="password"
                    class="form-control">
            </span>
        </div>
        <div>
            <slot></slot>
        </div>
        <div class="form-group text-right">
            <button type="submit"
                class="btn btn-green btn-lg"
                @click="$emit('input', {username: username, password: password})"
                :disabled="authenticating">
                <span v-if="!authenticating">
                    <span v-if="submitButtonText">{{ submitButtonText }}</span>
                    <span v-else>{{ $t('Sign In') }}</span>
                </span>
                <button-loading-dots v-else></button-loading-dots>
            </button>
        </div>
        <transition name="fade">
            <div class="error session-expired"
                v-if="error === 'sessionExpired'">
                <strong>{{ $t('Your session has been expired.') }}</strong>
                {{ $t('Please log in again to proceed.') }}
            </div>
        </transition>
        <transition name="fade">
            <div class="error locked"
                v-if="error === 'locked'">
                <strong>{{ $t('Your account has been locked.') }}</strong>
                {{ $t('Please wait a moment before the next login attempt.') }}
            </div>
        </transition>
        <transition name="fade">
            <div class="error disabled"
                v-if="error === 'disabled'">
                <p><strong>{{ $t('Your account has not been confirmed yet.') }}</strong></p>
                <p>{{ $t('Please click the link we have sent you after the registration to proceed.') }}</p>
                <p>
                    <resend-account-activation-link :username="username"></resend-account-activation-link>
                </p>
            </div>
        </transition>
        <transition name="fade">
            <div class="error"
                v-if="frontendConfig.maintenanceMode">
                <maintenance-warning></maintenance-warning>
            </div>
        </transition>
        <transition name="fade">
            <router-link to="/forgotten-password"
                class="error"
                v-if="error == 'error'">
                <strong>{{ $t('Forgot your password?') }}</strong>
                {{ $t('Don’t worry, you can always reset your password via email. Click here to do so.') }}
            </router-link>
        </transition>
        <div v-if="!oauth && !frontendConfig.maintenanceMode && frontendConfig.accountsRegistrationEnabled"
            class="additional-buttons">
            <router-link
                to="/forgotten-password"
                class="btn btn-white btn-wrapped">
                <span class="pe-7s-help1 mr-1 login-button-icon"></span>
                {{ $t('Forgot your password?') }}
            </router-link>
            <router-link to="/register"
                class="btn btn-white btn-wrapped">
                <img src="/assets/img/user.png">
                {{ $t('Create an account') }}
            </router-link>
        </div>
    </div>
</template>

<script>
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import ResendAccountActivationLink from "../register/resend-account-activation-link";
    import MaintenanceWarning from "../common/errors/maintenance-warning";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        props: ['authenticating', 'oauth', 'error', 'value', 'intitialUsername', 'submitButtonText'],
        components: {ResendAccountActivationLink, ButtonLoadingDots, MaintenanceWarning},
        data() {
            return {
                username: '',
                password: '',
            };
        },
        mounted() {
            this.username = this.intitialUsername || '';
        },
        computed: {
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";
    @import "../styles/mixins";

    .login-form {
        .logo {
            text-align: center;
            margin-bottom: 20px;

            img {
                width: 150px;
                height: 150px;
            }
        }

        .input-group-addon > span {
            font-size: 2em;
        }

        input[type=text], input[type=password], input[type=email] {
            &.form-control {
                border: 0;
                border-radius: 0;
                border-bottom: 1px solid $supla-grey-light;
                box-shadow: initial;
                background: transparent;
                padding-left: 0;
                padding-right: 0;
            }
        }

        .additional-buttons {
            display: flex;
            justify-content: space-between;
            > .btn {
                flex: 1;
                max-width: 48%;
                display: flex;
                justify-content: center;
                align-items: center;

                img {
                    float: left;
                    height: 23px;
                    margin-right: 5px;
                }

                .login-button-icon {
                    color: #23a618;
                    font-size: 1.5em;
                }
            }
        }

        .error {
            display: inline-block;
            background: $supla-yellow;
            padding: 12px 20px;
            margin-top: 15px;
            border-radius: 3px;
            color: $supla-black;
            margin-bottom: 20px;

            p a {
                color: darken($supla-green, 10%);
            }

            &.locked {
                background: $supla-red;
                color: $supla-white;
            }
        }
    }
</style>

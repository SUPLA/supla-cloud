<template>
    <div class="create-form">
        <h1 class="page-title" v-title>{{ $t('Create an account') }}</h1>

        <form @submit.prevent="submit()"
            class="register-form">
            <input type="hidden"
                name="timezone"
                :value="timezone">
            <div :class="['form-group', {'has-error': errorEmail}]">
                <label for="email">{{ $t('Enter your email address') }}</label>
                <input type="email"
                    id="email"
                    class="form-control"
                    autocorrect="off"
                    v-focus="true"
                    autocapitalize="none"
                    @blur="emailTouched = true"
                    v-model="username">
                <div class="help-block for-error-only">
                    {{ $t('Please enter a valid email address') }}
                </div>
            </div>
            <div :class="['form-group', {'has-error': passwordTouched && errorPassword}]">
                <label for="password">{{ $t('Enter strong password') }}</label>
                <input type="password"
                    @blur="passwordTouched = true"
                    id="password"
                    class="form-control"
                    v-model="password">
                <div class="help-block for-error-only">
                    {{ $t('The password should be 8 or more characters.') }}
                </div>
                <div class="help-block hidden-xs">
                    {{ $t('This password will protect your home. Therefore, it must:') }}
                    <ul class="fa-ul">
                        <li>
                            <fa-checklist-bullet :value="!errorPassword"/>
                            {{ $t('be at least {numChars} characters long', {numChars: 8}) }}
                        </li>
                    </ul>
                    {{ $t('We also strongly advise you to choose a password that:') }}
                    <ul class="fa-ul">
                        <li>
                            <fa-checklist-bullet :value="password.length >= 12"/>
                            {{ $t('is at least {numChars} characters long', {numChars: 12}) }}
                        </li>
                        <li>
                            <fa-checklist-bullet :value="!!(password.match(/[A-Z]/) && password.match(/[a-z]/))"/>
                            {{ $t('contains lower and upper letters') }}
                        </li>
                        <li>
                            <fa-checklist-bullet :value="!!password.match(/[0-9]/)"/>
                            {{ $t('contains a digit') }}
                        </li>
                        <li>
                            <fa-checklist-bullet :value="!!password.match(/[^0-9a-z]/i)"/>
                            {{ $t('contains a character that is neither a letter nor a digit') }}
                        </li>
                    </ul>
                </div>
            </div>
            <div :class="['form-group', {'has-error': errorPasswordConfirm}]">
                <label for="password-confirm">{{ $t('Repeat password') }}</label>
                <input type="password"
                    id="password-confirm"
                    @blur="passwordConfirmTouched = true"
                    class="form-control"
                    v-model="confirmPassword">
                <div class="help-block for-error-only">
                    {{ $t('The password and its confirm are not the same.') }}
                </div>
            </div>

            <div :class="['form-group', {'has-error': errorRegulations}]">
                <regulations-checkbox v-model="regulationsAgreed"
                    @input="regulationsTouched = true"
                    v-if="regulationsAcceptRequired"/>
                <div class="help-block for-error-only">
                    {{ $t('You must agree to the Terms and Conditions.') }}
                </div>
            </div>
            <transition-expand>
                <div class="alert error"
                    v-if="errorMessage">
                    <p>{{ errorMessage }}</p>
                    <p v-if="resendActivationLinkOption">
                        <resend-account-activation-link :username="username"></resend-account-activation-link>
                    </p>
                </div>
            </transition-expand>

            <div v-if="captchaEnabled">
                <invisible-recaptcha
                    :sitekey="captchaSiteKey"
                    :callback="checkCaptcha"
                    id="registerRecaptcha"
                    :disabled="isBusy"
                    :form-valid="formIsValid">
                    <template>
                        <span v-if="!isBusy">
                            {{ $t('Create an account') }}
                        </span>
                        <button-loading-dots v-else></button-loading-dots>
                    </template>
                </invisible-recaptcha>
            </div>
            <div v-else>
                <button type="submit"
                    class="btn btn-default btn-block btn-lg">
                    <span v-if="!isBusy">
                        {{ $t('Create an account') }}
                    </span>
                    <button-loading-dots v-else></button-loading-dots>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
    import ButtonLoadingDots from '../common/gui/loaders/button-loading-dots.vue';
    import InvisibleRecaptcha from './invisible-recaptcha.vue';
    import RegulationsCheckbox from "../common/errors/regulations-checkbox";
    import ResendAccountActivationLink from "./resend-account-activation-link";
    import {DateTime} from "luxon";
    import FaChecklistBullet from "@/register/fa-checklist-bullet";
    import TransitionExpand from "@/common/gui/transition-expand";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        components: {
            TransitionExpand,
            FaChecklistBullet, ResendAccountActivationLink, RegulationsCheckbox, ButtonLoadingDots, InvisibleRecaptcha
        },

        data() {
            return {
                username: '',
                password: '',
                confirmPassword: '',
                timezone: DateTime.local().setZone("system").zoneName || 'Europe/Warsaw',
                isBusy: false,
                errorMessage: '',
                captchaToken: null,
                regulationsAgreed: false,
                resendActivationLinkOption: false,
                emailTouched: false,
                passwordTouched: false,
                passwordConfirmTouched: false,
                regulationsTouched: false,
            };
        },
        computed: {
            errorEmail() {
                return this.emailTouched && this.username.indexOf('@') === -1;
            },
            errorPassword() {
                return this.password.length < 8;
            },
            errorPasswordConfirm() {
                return this.passwordConfirmTouched && this.password !== this.confirmPassword;
            },
            errorRegulations() {
                return this.regulationsTouched && !this.regulationsAgreed && this.regulationsAcceptRequired;
            },
            formIsValid() {
                return !this.errorEmail && !this.errorPassword && !this.errorPasswordConfirm && !this.errorRegulations;
            },
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
            captchaEnabled() {
                return this.frontendConfig.recaptchaEnabled;
            },
            captchaSiteKey() {
                return this.frontendConfig.recaptchaSiteKey;
            },
            regulationsAcceptRequired() {
                return this.frontendConfig.regulationsAcceptRequired;
            },
        },
        methods: {
            checkCaptcha(recaptchaToken) {
                this.captchaToken = this.captchaEnabled ? recaptchaToken : null;
                this.submit();
            },
            submit() {
                this.resendActivationLinkOption = false;
                this.emailTouched = true;
                this.passwordTouched = true;
                this.passwordConfirmTouched = true;
                this.regulationsTouched = true;
                this.errorMessage = '';
                if (!this.formIsValid) {
                    return;
                }
                const data = {
                    email: this.username,
                    password: this.password,
                    timezone: this.timezone,
                    locale: this.$i18n.locale,
                    regulationsAgreed: this.regulationsAgreed
                };
                if (this.captchaEnabled) {
                    data.captcha = this.captchaToken;
                }

                this.isBusy = true;
                this.$http.post('register-account', data, {skipErrorHandler: [400, 409]})
                    .then(({body}) => this.$emit('registered', body.email))
                    .catch(({body}) => {
                        this.resendActivationLinkOption = body.accountEnabled === false;
                        this.errorMessage = this.$t(body.message);
                    })
                    .finally(() => this.isBusy = false);
            }
        }
    };
</script>

<template>
    <div class="create-form">
        <h1 class="page-title"
            v-title>{{ $t('Create an account') }}</h1>

        <div class="alert error"
            v-if="errorMessage">
            <p>{{ errorMessage }}</p>
            <p v-if="resendActivationLinkOption">
                <resend-account-activation-link :username="username"></resend-account-activation-link>
            </p>
        </div>

        <form @submit.prevent="submit()"
            class="register-form">
            <input type="hidden"
                name="timezone"
                :value="timezone">

            <input type="email"
                class="form-input"
                autocorrect="off"
                v-focus="true"
                autocapitalize="none"
                :placeholder="$t('Enter your email address')"
                v-model="username">

            <input type="password"
                class="form-input"
                :placeholder="$t('Enter strong password')"
                v-model="password">

            <input type="password"
                class="form-input"
                :placeholder="$t('Repeat password')"
                v-model="confirmPassword">

            <regulations-checkbox v-model="regulationsAgreed"
                v-if="regulationsAcceptRequired"></regulations-checkbox>

            <div v-if="captchaEnabled">
                <invisible-recaptcha
                    :sitekey="captchaSiteKey"
                    :callback="checkCaptcha"
                    id="registerRecaptcha"
                    type="submit"
                    :disabled="isBusy"
                    :form-valid="!computedErrorMessage">
                    <template slot-scope="btn">
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
    import Vue from 'vue';
    import ButtonLoadingDots from '../common/gui/loaders/button-loading-dots.vue';
    import InvisibleRecaptcha from './invisible-recaptcha.vue';
    import RegulationsCheckbox from "../common/errors/regulations-checkbox";
    import ResendAccountActivationLink from "./resend-account-activation-link";

    export default {
        components: {ResendAccountActivationLink, RegulationsCheckbox, ButtonLoadingDots, InvisibleRecaptcha},

        data() {
            return {
                username: '',
                password: '',
                confirmPassword: '',
                timezone: moment.tz.guess() || 'Europe/Warsaw',
                isBusy: false,
                errorMessage: '',
                regulationsAcceptRequired: Vue.config.external.regulationsAcceptRequired,
                captchaEnabled: Vue.config.external.recaptchaEnabled,
                captchaSiteKey: Vue.config.external.recaptchaSiteKey,
                captchaToken: null,
                regulationsAgreed: false,
                resendActivationLinkOption: false
            };
        },
        computed: {
            computedErrorMessage() {
                let errorMessage = '';
                if (this.username.indexOf('@') <= 0) {
                    errorMessage = this.$t('Please enter a valid email address');
                } else if (this.password.length < 8) {
                    errorMessage = this.$t('The password should be 8 or more characters.');
                } else if (this.password != this.confirmPassword) {
                    errorMessage = this.$t('The password and its confirm are not the same.');
                } else if (!this.regulationsAgreed && this.regulationsAcceptRequired) {
                    errorMessage = this.$t('You must agree to the Terms and Conditions.');
                }
                return errorMessage;
            }
        },
        methods: {
            checkCaptcha(recaptchaToken) {
                this.captchaToken = this.captchaEnabled ? recaptchaToken : null;
                this.submit();
            },
            submit() {
                this.resendActivationLinkOption = false;
                this.errorMessage = this.computedErrorMessage;
                if (this.errorMessage) {
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

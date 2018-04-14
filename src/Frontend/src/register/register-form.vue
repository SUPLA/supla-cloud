<template>
    <div class="create-form">
        <div class="wrapper">
            <h1 class="page-title"
                v-title>{{ $t('Create an account') }}</h1>

            <div class="error"
                v-if="errorMessage">
                {{ errorMessage }}
            </div>

            <form @submit.prevent="submit()"
                class="register-form">
                <input type="hidden"
                    name="timezone"
                    :value="timezone">

                <input type="email"
                    class="form-input"
                    autocorrect="off"
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

                <regulations-checkbox v-model="regulationsAgreed"></regulations-checkbox>

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
    </div>
</template>

<script>
    import Vue from 'vue';
    import ButtonLoadingDots from '../common/gui/loaders/button-loading-dots.vue';
    import InvisibleRecaptcha from './invisible-recaptcha.vue';
    import RegulationsCheckbox from "../common/errors/regulations-checkbox";

    export default {
        components: {RegulationsCheckbox, ButtonLoadingDots, InvisibleRecaptcha},

        data() {
            return {
                username: '',
                password: '',
                confirmPassword: '',
                timezone: moment.tz.guess() || 'Europe/Warsaw',
                isBusy: false,
                errorMessage: '',
                captchaEnabled: Vue.config.external.recaptchaEnabled,
                captchaSiteKey: Vue.config.external.recaptchaSiteKey,
                captchaToken: null,
                regulationsAgreed: false
            };
        },
        computed: {
            computedErrorMessage() {
                let errorMessage = '';
                if (this.username.indexOf('@') <= 0) {
                    errorMessage = this.$t('Please fill a valid email address');
                } else if (this.password.length < 8) {
                    errorMessage = this.$t('The password should be 8 or more characters.');
                } else if (this.password != this.confirmPassword) {
                    errorMessage = this.$t('The password and its confirm are not the same.');
                } else if (!this.regulationsAgreed) {
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
                this.errorMessage = this.computedErrorMessage;
                if (this.errorMessage) {
                    return;
                }
                let data = {
                    email: this.username,
                    password: this.password,
                    timezone: this.timezone,
                    regulationsAgreed: this.regulationsAgreed
                };
                if (this.captchaEnabled) {
                    data.captcha = this.captchaToken;
                }

                this.isBusy = true;
                this.$http.post('register', data).then(({body}) => {
                    this.isBusy = false;
                    this.$emit('registered', body.email);
                }).catch(({body}) => {
                    this.isBusy = false;
                    this.errorMessage = this.$t(body.message);
                });
            }
        }
    };
</script>

<style lang="scss">
    @import '../styles/variables';

    .create-form {
        color: $supla-white;

        .checkbox a {
            color: $supla-yellow;
        }

        @media screen and (max-width: 899px) {
            padding: 15px;
        }

        @media screen and (min-width: 900px) {
            display: table;
            height: 100%;
            max-width: 400px;
            position: absolute;
            top: 0px;
            left: 55%;
            z-index: 50;
        }
        .register-form {
            max-width: 305px;
        }

        .page-title {
            margin-bottom: 20px;
        }

        .error {
            color: $supla-red;
            margin: 3px 0 10px;
        }

        .form-input {
            padding: 0px;
            border: none;
            border-bottom: solid 1px rgba(255, 255, 255, 0.7);
            font-size: 18px;
            line-height: 36px;
            width: 100%;
            background: none;
            margin-bottom: 3px;
            font-weight: 300;
            color: #000;
            transition: all 0.3s;
            color: #fff;
            margin-top: 10px;
            max-width: 301px;
            height: 37px;
        }

        .form-input::-webkit-input-placeholder {
            color: #fff;
            opacity: 0.8;
        }
        .form-input::-moz-placeholder {
            color: #fff;
            opacity: 0.8;
        }
        .form-input:-moz-placeholder {
            color: #fff;
            opacity: 0.8;
        }
        .form-input:-ms-input-placeholder {
            color: #fff;
            opacity: 0.8;
        }

        .form-input:focus,
        .form-input:hover {
            border-bottom-color: #fff;
        }
    }

    @media screen and (max-width: 899px) {
        .create-form {
            h1 {
                text-align: center;
            }
            form {
                margin: 0 auto;
            }
        }
    }

    @media screen and (min-width: 900px) {
        .create-form .wrapper {
            display: table-cell;
            height: 100%;
            margin: 0 auto;
            vertical-align: middle;
        }
    }
</style>

<template>
    <page-container :error="error">
        <transition name="fade-router">
            <whole-screen-message v-if="token"
                class="container"
                icon="pe-7s-global"
                header="Your private SUPLA Cloud is almost registered."
                message="You just need to execute the following command inside your host terminal.">
                <div class="flex-left-full-width">
                    <pre style="overflow: hidden"><code style="white-space: nowrap; overflow: hidden">{{ tokenCommand }}</code></pre>
                    <copy-button :text="tokenCommand"
                        default-class="black"></copy-button>
                </div>
            </whole-screen-message>
        </transition>
        <transition name="fade-router">
            <div v-if="!token">
                <register-slider :texts="['register-target-cloud-slide1', 'register-target-cloud-slide2', 'register-target-cloud-slide3']"></register-slider>
                <div class="create-form">
                    <div class="wrapper">
                        <h1 class="page-title"
                            v-title>
                            {{ $t('Register private SUPLA Cloud') }}</h1>

                        <div class="error"
                            v-if="errorMessage">
                            {{ errorMessage }}
                        </div>

                        <form @submit.prevent="registerTargetCloud()"
                            class="register-form">
                            <input type="email"
                                class="form-input"
                                autocorrect="off"
                                autocapitalize="none"
                                required
                                :placeholder="$t('Enter your email address')"
                                v-model="email">
                            <span class="help-block">Użyjemy go wyłącznie w uzasadnionych przypadkach.</span>


                            <input type="text"
                                class="form-input"
                                autocorrect="off"
                                required
                                autocapitalize="none"
                                :placeholder="$t('Where is your SUPLA Cloud?')"
                                v-model="targetCloud">
                            <span class="help-block">Podaj tylko domenę lub adres IP, razem z portem jeśli nie jest standardowy (443). Wymagamy połączenia HTTPS.</span>

                            <regulations-checkbox v-model="regulationsAgreed"></regulations-checkbox>

                            <invisible-recaptcha
                                :sitekey="captchaSiteKey"
                                :callback="registerTargetCloud"
                                id="registerRecaptcha"
                                type="submit"
                                :disabled="isBusy"
                                :form-valid="!computedErrorMessage"
                                btn-class="btn-black">
                                <template slot-scope="btn">
                                    <span v-if="!isBusy">
                                        {{ $t('Register') }}
                                    </span>
                                    <button-loading-dots v-else></button-loading-dots>
                                </template>
                            </invisible-recaptcha>

                        </form>
                    </div>
                </div>
            </div>
        </transition>
    </page-container>
</template>

<script>
    import RegisterSlider from '../register/register-slider';
    import RegulationsCheckbox from "../common/errors/regulations-checkbox";
    import InvisibleRecaptcha from "../register/invisible-recaptcha";
    import Vue from "vue";
    import ButtonLoadingDots from '../common/gui/loaders/button-loading-dots.vue';
    import PageContainer from "../common/pages/page-container";
    import CopyButton from "../common/copy-button";
    import WholeScreenMessage from "../register/whole-screen-message";

    export default {
        components: {
            WholeScreenMessage,
            CopyButton, PageContainer, InvisibleRecaptcha, RegulationsCheckbox, RegisterSlider, ButtonLoadingDots
        },
        data() {
            return {
                email: '',
                targetCloud: '',
                isBusy: false,
                captchaSiteKey: Vue.config.external.recaptchaSiteKey,
                error: Vue.config.external.actAsBrokerCloud ? 0 : 404,
                regulationsAgreed: false,
                errorMessage: '',
                token: undefined,
            };
        },
        mounted() {
            if (!this.error) {
                document.body.setAttribute('class', 'blue');
            }
        },
        computed: {
            computedErrorMessage() {
                let errorMessage;
                if (this.email.indexOf('@') <= 0) {
                    errorMessage = this.$t('Please fill a valid email address');
                } else if (this.targetCloud.indexOf('.') <= 0) {
                    errorMessage = this.$t('Please provide a valid domain name for your private SUPLA Cloud');
                } else if (!this.regulationsAgreed) {
                    errorMessage = this.$t('You must agree to the Terms and Conditions.');
                }
                return errorMessage;
            },
            tokenCommand() {
                return `docker exec -it -u www-data supla-cloud php bin/console supla:register-target-cloud ${this.token}`;
            }
        },
        methods: {
            registerTargetCloud(captcha) {
                this.errorMessage = this.computedErrorMessage;
                if (this.errorMessage) {
                    return;
                }
                const data = {
                    email: this.email,
                    targetCloud: this.targetCloud,
                    captcha
                };
                this.isBusy = true;
                this.$http.post('register-target-cloud', data)
                    .then(({body}) => this.token = body.token)
                    .catch(({body}) => this.errorMessage = this.$t(body.message))
                    .finally(() => this.isBusy = false);
            }
        }
    };
</script>

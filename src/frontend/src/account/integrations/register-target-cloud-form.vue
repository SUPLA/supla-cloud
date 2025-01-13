<template>
    <page-container :error="error">
        <transition name="fade-router">
            <whole-screen-message v-if="token"
                class="container"
                icon="pe-7s-global"
                header-i18n="Your private SUPLA Cloud is almost registered."
                :message="$t('You just need to execute the following command inside your host terminal.')">
                <div class="flex-left-full-width">
                    <pre style="overflow: hidden"><code style="white-space: nowrap; overflow: hidden">{{ tokenCommand }}</code></pre>
                    <copy-button :text="tokenCommand"
                        default-class="black"></copy-button>
                </div>
            </whole-screen-message>
        </transition>
        <transition name="fade-router">
            <whole-screen-message v-if="unregistered"
                class="container"
                icon="pe-7s-mail"
                header-i18n="You have requested to unregister your SUPLA Cloud instance"
                :message="$t('We have sent you an email with further instructions.')">
            </whole-screen-message>
        </transition>
        <transition name="fade-router">
            <div v-if="!token && !unregistered">
                <!-- i18n:['register-slide1-text','register-slide1-title','register-slide2-text','register-slide2-title','register-slide3-text','register-slide3-title'] -->
                <div class="register-page">
                    <div class="register-slider-container">
                        <register-slider :texts="['register-slide1', 'register-slide2', 'register-slide3']"></register-slider>
                    </div>
                    <div class="create-form-container">
                        <div class="create-form">
                            <h1 class="page-title"
                                v-title>
                                {{ $t('Register private SUPLA Cloud') }}</h1>

                            <form @submit.prevent="submit()"
                                class="register-form">
                                <div :class="['form-group', {'has-error': errorEmail}]">
                                    <label for="email">{{ $t('Enter your email address') }}</label>
                                    <input type="email"
                                        class="form-control"
                                        autocorrect="off"
                                        autocapitalize="none"
                                        @blur="emailTouched = true"
                                        id="email"
                                        v-focus="true"
                                        v-model="email">
                                    <div class="help-block for-error-only">{{ $t('Please enter a valid email address') }}</div>
                                    <span class="help-block">{{ $t('We will use it only in justified situations.') }}</span>
                                </div>

                                <div :class="['form-group', {'has-error': errorUrl}]">
                                    <label for="url">{{ $t('Where is your SUPLA Cloud?') }}</label>
                                    <input type="text"
                                        class="form-control"
                                        autocorrect="off"
                                        autocapitalize="none"
                                        id="url"
                                        @blur="urlTouched = true"
                                        v-model="targetCloud">
                                    <div
                                        class="help-block for-error-only">{{ $t('Please provide a valid domain name for your private SUPLA Cloud') }}
                                    </div>
                                    <span class="help-block">
                                        {{ $t('Enter the domain with the port only if it is not standard (443). We require HTTPS connection.') }}
                                    </span>
                                </div>
                                <div :class="['form-group', {'has-error': errorRegulations}]">
                                    <regulations-checkbox v-model="regulationsAgreed" @input="regulationsTouched = true"/>
                                    <div class="help-block for-error-only">
                                        {{ $t('You must agree to the Terms and Conditions.') }}
                                    </div>
                                </div>

                                <transition-expand>
                                    <div class="alert error" v-if="errorMessage">
                                        <p>{{ errorMessage }}</p>
                                    </div>
                                </transition-expand>

                                <invisible-recaptcha
                                    :sitekey="captchaSiteKey"
                                    :callback="submit"
                                    id="registerRecaptcha"
                                    :disabled="isBusy"
                                    :form-valid="formIsValid"
                                    btn-class="btn-black btn btn-block btn-lg">
                                    <template>
                                        <span v-if="!isBusy">
                                            <span v-if="unregister">{{ $t('Undo registration') }}</span>
                                            <span v-else>{{ $t('Register') }}</span>
                                        </span>
                                        <button-loading-dots v-else></button-loading-dots>
                                    </template>
                                </invisible-recaptcha>

                                <div class="text-right mt-3 small"
                                    v-if="!isBusy">
                                    <i18n-t keypath="If you wish to register your instance, {clickHereLink}."
                                        v-if="unregister">
                                        <template #clickHereLink>
                                            <a @click.prevent="unregister = false">{{ $t('click here') }}</a>
                                        </template>
                                    </i18n-t>
                                    <i18n-t keypath="If you wish to unregister your instance, {clickHereLink}."
                                        v-else>
                                        <template #clickHereLink>
                                            <a @click.prevent="unregister = true">{{ $t('click here') }}</a>
                                        </template>
                                    </i18n-t>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </page-container>
</template>

<script>
    import RegisterSlider from '../../register/register-slider.vue';
    import RegulationsCheckbox from "../../common/errors/regulations-checkbox.vue";
    import InvisibleRecaptcha from "../../register/invisible-recaptcha.vue";
    import ButtonLoadingDots from '../../common/gui/loaders/button-loading-dots.vue';
    import PageContainer from "../../common/pages/page-container.vue";
    import CopyButton from "../../common/copy-button.vue";
    import WholeScreenMessage from "../../register/whole-screen-message.vue";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        components: {
            WholeScreenMessage,
            CopyButton, PageContainer, InvisibleRecaptcha, RegulationsCheckbox, RegisterSlider, ButtonLoadingDots
        },
        data() {
            return {
                email: '',
                targetCloud: this.$route.query.domain || '',
                isBusy: false,
                regulationsAgreed: false,
                errorMessage: '',
                token: undefined,
                unregister: false,
                unregistered: false,
                emailTouched: false,
                urlTouched: false,
                regulationsTouched: false,
            };
        },
        mounted() {
            if (!this.error) {
                document.body.setAttribute('class', document.body.getAttribute('class') + ' blue');
            }
        },
        computed: {
            errorEmail() {
                return this.emailTouched && this.email.indexOf('@') === -1;
            },
            errorUrl() {
                return this.urlTouched && this.targetCloud.indexOf('.') === -1;
            },
            errorRegulations() {
                return this.regulationsTouched && !this.regulationsAgreed;
            },
            formIsValid() {
                return !this.errorEmail && !this.errorUrl && !this.errorRegulations;
            },
            tokenCommand() {
                return `docker exec -it -u www-data supla-cloud php bin/console supla:register-target-cloud ${this.token}`;
            },
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
            captchaSiteKey() {
                return this.frontendConfig.recaptchaSiteKey;
            },
            error() {
                return this.frontendConfig.actAsBrokerCloud ? 0 : 404;
            },
        },
        methods: {
            submit(captcha) {
                this.emailTouched = true;
                this.urlTouched = true;
                this.regulationsTouched = true;
                this.errorMessage = '';
                if (!this.formIsValid) {
                    return;
                }
                const data = {
                    email: this.email,
                    targetCloud: this.targetCloud,
                    captcha
                };
                this.isBusy = true;
                if (this.unregister) {
                    this.unregisterTargetCloud(data);
                } else {
                    this.registerTargetCloud(data);
                }
            },
            registerTargetCloud(data) {
                this.$http.post('register-target-cloud', data, {skipErrorHandler: true})
                    .then(({body}) => this.token = body.token)
                    .catch(({body, status}) => {
                        const message = this.$t(body.message || 'Could not contact Autodiscover service. Try again in a while.');
                        this.errorMessage = `${message} (${this.$t('Error')}: ${status})`;
                    })
                    .finally(() => this.isBusy = false);
            },
            unregisterTargetCloud(data) {
                this.$http.post('remove-target-cloud', data, {skipErrorHandler: true})
                    .then(() => this.unregistered = true)
                    .catch(({body, status}) => {
                        const message = this.$t(body.message || 'Could not contact Autodiscover service. Try again in a while.');
                        this.errorMessage = `${message} (${this.$t('Error')}: ${status})`;
                    })
                    .finally(() => this.isBusy = false);
            },
        }
    };
</script>

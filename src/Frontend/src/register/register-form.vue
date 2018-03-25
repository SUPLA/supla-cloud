<template>
    <div class="create-form">
        <div class="wrapper">
            <h1 class="page-title">{{ $t('Create an account') }}</h1>

            <div class="error" v-if="isError">{{ errorMessage }}</div>

            <form method="post" ref="registerForm"
                  @submit.prevent="submit()" class="register-form">
                <input type="hidden" name="timezone" :value="timezone">

                <input type="email" class="form-input" required
                       autocorrect="off" autocapitalize="none"
                       :placeholder="$t('Enter your email address')"
                       v-model="username">

                <input type="password" class="form-input" required
                       :placeholder="$t('Enter strong password')"
                       v-model="password">

                <input type="password" class="form-input" required
                       :placeholder="$t('Repeat password')"
                       v-model="confirm">

                <div v-if="captchaEnabled">
                    <invisible-recaptcha :sitekey="captchaSiteKey" :callback="checkCaptcha"
                                         id="create_account" type="submit"
                                         :disabled="isBusy" class="btn btn-default btn-create-account">
                        <span v-if="!isBusy">
                            {{ $t('Create an account') }}
                        </span>
                        <button-loading-dots v-else></button-loading-dots>
                    </invisible-recaptcha>
                </div>
                <div v-else>
                    <button type="submit" class="btn btn-default btn-create-account">
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
import InvisibleRecaptcha from '../common/invisible-recaptcha.vue';

export default {
    components: { ButtonLoadingDots, InvisibleRecaptcha },

    data () {
        return {
            username: '',
            password: '',
            confirm: '',
            timezone: 'Europe/Warsaw',
            isError: false,
            isBusy: false,
            errorMessage: '',
            captchaEnabled: Vue.config.external.recaptchaEnabled,
            captchaSiteKey: Vue.config.external.recaptchaSiteKey,
            captchaToken: null
        };
    },
    methods: {
        checkCaptcha (recaptchaToken) {
            this.captchaToken = this.captchaEnabled ? recaptchaToken : null;
            this.submit();
        },
        submit () {
            let data = {
                username: this.username,
                email: this.username,
                plainPassword: {
                    password: this.password,
                    confirm: this.confirm,
                },
                timezone: this.timezone
            };
            if (this.captchaEnabled) {
                data.captcha = this.captchaToken;
            }

            this.isBusy = true;
            this.isError = false;
            this.$http.post('account-create', data).then(({body}) => {
                this.$store.setCreatedUserAction(body.username);
                this.isBusy = false;
                this.$router.push("/check-email");
            }).catch(({body}) => {
                this.isError = true;
                this.isBusy = false;
                this.errorMessage = this.$t(body.message);
            });
        }
    },
    mounted () {
        $("#user-timezone-field").val(moment.tz.guess());
    }
};
</script>
<style lang="scss">

.create-form {

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
        color: #fff;
        margin-bottom: 20px;
    }

    .error {
        color: #FFE804;
        margin: 3px 0 10px;
    }

    .form-input {
        padding: 0px;
        border: none;
        border-bottom: solid 1px rgba(255,255,255,0.7);
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

.btn-create-account {
    background:  #fff;
    border: solid 1px #fff;
    border-radius: 3px;
    width: 100%;
    margin-top: 20px;
    max-width: 301px;
    line-height: 30px;
    font-size: 13px;
    text-transform: uppercase;
    color: #000;
    transition: all 0.3s;

    &:hover {
        background: #F0F1F4;
        border-color: #F0F1F4;
        color: #000;
        transition: all 0.3s;
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

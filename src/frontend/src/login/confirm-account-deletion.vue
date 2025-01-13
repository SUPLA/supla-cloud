<template>
    <div class="container text-center">
        <loading-cover :loading="!tokenExists" class="mt-5"/>
        <div v-if="tokenExists">
            <h1 v-title>{{ $t('We will miss you!') }}</h1>
            <p class="text-center">{{ $t('Deleting your account will result also in deletion of all your data, including your connected devices, configured channels, direct links and measurement history. Deleting an account is irreversible.') }}</p>
            <p class="">{{ $t('This is the final step in the deletion process. Completing the form will result in an irreversible data loss.') }}</p>
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="username">{{ $t('Your email') }}</label>
                        <input type="email" id="username" class="form-control" autocomplete="new-password" v-model="username">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="password">{{ $t('Password') }}</label>
                        <input type="password" id="password" class="form-control" autocomplete="new-password" v-model="password">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="checkbox2">
                    <input type="checkbox" v-model="understood">
                    <span>
                        {{ $t('I understand that my SUPLA account will be permanently deleted.') }}
                    </span>
                </label>
            </div>
            <div class="row">
                <div class="col-sm-6 text-left">
                    <router-link to="/" class="btn btn-green btn-lg">
                        {{ $t('No, take me back!') }}
                    </router-link>
                </div>
                <div class="col-sm-6 text-right">
                    <div v-if="captchaEnabled">
                        <invisible-recaptcha
                            :sitekey="captchaSiteKey"
                            :callback="checkCaptcha"
                            id="registerRecaptcha"
                            btn-class="btn btn-danger"
                            :disabled="isBusy"
                            :form-valid="!!requestData">
                            <template>
                                <span v-if="!isBusy">
                                    {{ $t('Delete my account') }}
                                </span>
                                <button-loading-dots v-else></button-loading-dots>
                            </template>
                        </invisible-recaptcha>
                    </div>
                    <div v-else>
                        <button type="button"
                            @click="confirmDeletion()"
                            :disabled="!requestData"
                            class="btn btn-danger">
                            <span v-if="!isBusy">
                                {{ $t('Delete my account') }}
                            </span>
                            <button-loading-dots v-else></button-loading-dots>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {errorNotification, successNotification} from "../common/notifier";
    import InvisibleRecaptcha from "@/register/invisible-recaptcha";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        components: {InvisibleRecaptcha},
        props: ['token'],
        data() {
            return {
                username: '',
                password: '',
                tokenExists: true,
                isBusy: false,
                understood: false,
            };
        },
        mounted() {
            this.$http.get('account-deletion/' + this.token, {skipErrorHandler: [400]})
                .then(() => this.tokenExists = true)
                .catch(() => {
                    errorNotification(this.$t('Error'), this.$t('Token does not exist'));
                    this.$router.push({name: 'login'});
                });
        },
        methods: {
            checkCaptcha(captchaCode) {
                this.confirmDeletion(captchaCode);
            },
            confirmDeletion(captchaCode) {
                this.isBusy = true;
                const requestData = {...this.requestData, captchaCode};
                this.$http.patch('account-deletion', requestData, {skipErrorHandler: [400]})
                    .then(() => {
                        successNotification(this.$t('Successful'), this.$t('Your account has been deleted. We hope you will come back to us soon.'));
                        this.$router.push({name: 'login'});
                    })
                    .catch(() => errorNotification(this.$t('Error'), this.$t('Invalid username or password')))
                    .finally(() => this.isBusy = false);
            },
        },
        computed: {
            requestData() {
                if (this.understood && this.username.indexOf('@') > 0 && this.password.length > 0) {
                    return {username: this.username, password: this.password, token: this.token};
                } else {
                    return undefined;
                }
            },
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
            captchaEnabled() {
                return this.frontendConfig.recaptchaEnabled;
            },
            captchaSiteKey() {
                return this.frontendConfig.recaptchaSiteKey;
            },
        }
    };
</script>

<style scoped>
    p {
        font-size: 1.3em;
        margin-bottom: 2em;
    }
</style>

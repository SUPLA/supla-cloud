<template>
    <div class="container text-center">
        <h1 v-title>{{ $t('We will miss you!') }}</h1>
        <p class="text-center">{{ $t('Deleting your account will result also in deletion of all your data, including your connected devices, configured channels, direct links and measurement history. Deleting an account is irreversible.') }}</p>
        <div v-if="isSent">
            <fa icon="check" fixed-width size="xl"/>
            <p class="my-3">{{ $t('We have sent you an e-mail message with a delete confirmation link. Just to be sure!') }}</p>
            <a href="?ack=true" class="btn btn-green btn-lg">{{ $t('Close') }}</a>
        </div>
        <div v-else>
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
            <div class="text-center mb-3">
                <div v-if="captchaEnabled">
                    <invisible-recaptcha
                        :sitekey="captchaSiteKey"
                        :callback="checkCaptcha"
                        id="registerRecaptcha"
                        btn-class="btn btn-danger"
                        :disabled="isBusy"
                        :form-valid="!!requestData">
                        <template>
                            <span v-if="!isBusy">{{ $t('Delete my account') }}</span>
                            <button-loading-dots v-else/>
                        </template>
                    </invisible-recaptcha>
                </div>
                <div v-else>
                    <button type="button"
                        @click="confirmDeletion()"
                        :disabled="!requestData"
                        class="btn btn-danger">
                        <span v-if="!isBusy">{{ $t('Delete my account') }}</span>
                        <button-loading-dots v-else/>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {errorNotification} from "../common/notifier";
    import InvisibleRecaptcha from "@/register/invisible-recaptcha";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        components: {InvisibleRecaptcha},
        data() {
            return {
                username: '',
                password: '',
                isBusy: false,
                isSent: false,
            };
        },
        methods: {
            checkCaptcha(captchaCode) {
                this.confirmDeletion(captchaCode);
            },
            confirmDeletion(captchaCode) {
                this.isBusy = true;
                const requestData = {...this.requestData, captchaCode};
                this.$http.put('account-deletion', requestData, {skipErrorHandler: [400]})
                    .then(() => this.isSent = true)
                    .catch(() => errorNotification(this.$t('Error'), this.$t('Invalid username or password')))
                    .finally(() => this.isBusy = false);
            },
        },
        computed: {
            requestData() {
                if (this.username.indexOf('@') > 0 && this.password.length > 0) {
                    return {username: this.username, password: this.password};
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

<style scoped lang="scss">
    @import "../styles/mixins";

    p {
        font-size: 1.3em;
        margin-bottom: 2em;
    }
</style>

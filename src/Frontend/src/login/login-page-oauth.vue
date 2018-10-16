<template>
    <form @submit="authenticating = true"
        :action="askForTargetCloud ? '/oauth/v2/broker_login' : '/oauth/v2/auth_login'"
        ref="loginForm"
        method="post"
        v-title="$t('Login')"
        class="login-oauth-form">
        <login-form :authenticating="authenticating"
            :error="error"
            :intitial-username="lastUsername"
            :submit-button-text="askForTargetCloud ? 'Proceed to authentication' : ''">
            <div v-if="askForTargetCloud">
                <div class="form-group text-center">
                    <label>
                        <toggler v-model="ownCloud"
                            @input="error = undefined"></toggler>
                        {{ $t('Connect to SUPLA Cloud instance hosted by myself') }}
                    </label>
                </div>
                <div class="form-group form-group-lg"
                    v-if="ownCloud">
                    <span class="input-group">
                        <span class="input-group-addon">
                            <span class="pe-7s-global"></span>
                        </span>
                        <input type="text"
                            required
                            autocorrect="off"
                            autocapitalize="none"
                            :placeholder="$t('Your Cloud domain name')"
                            v-model="targetCloud"
                            name="targetCloud"
                            class="form-control">
                    </span>
                    <span class="help-block">
                        {{ $t('Only domain name or IP address, port included, e.g. mysupla.org or 1.2.3.4:88. HTTPS is required.') }}
                    </span>
                </div>
                <transition name="fade">
                    <div class="error"
                        v-if="error == 'autodiscover_fail'">
                        <div v-if="ownCloud">
                            <strong>{{ $t('We could not connect to your SUPLA Cloud instance.') }}</strong>
                            {{ $t('You either did not register your instance or you are trying to authorize an application that is not public.') }}
                        </div>
                        <div v-else>
                            <strong>{{ $t('We were not able to find your account.') }}</strong>
                            {{ $t('If you are sure you have an account on cloud.supla.org, check if the application you are trying to authorize is public.') }}
                        </div>
                    </div>
                </transition>
            </div>
        </login-form>
        <login-footer remind-password-link="true"></login-footer>
    </form>
</template>

<script>
    import LoginFooter from "./login-footer.vue";
    import LoginForm from "./login-form";

    export default {
        props: ['lastUsername', 'error', 'askForTargetCloud', 'lastTargetCloud'],
        components: {LoginForm, LoginFooter},
        data() {
            return {
                ownCloud: false,
                authenticating: false,
                displayError: false,
                targetCloud: '',
            };
        },
        mounted() {
            if (this.lastTargetCloud) {
                this.ownCloud = true;
                this.targetCloud = this.lastTargetCloud;
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .login-oauth-form {
        .login-password {
            display: none;
        }
    }
</style>

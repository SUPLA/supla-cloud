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
                        <toggler v-model="ownCloud"></toggler>
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
                            v-model="cloudAddress"
                            name="cloudAddress"
                            class="form-control">
                    </span>
                    <span class="help-block">
                        {{ $t('Only domain name or IP address, port included, e.g. mysupla.org or 1.2.3.4:88. HTTPS is required.') }}
                    </span>
                </div>
            </div>
        </login-form>
        <login-footer remind-password-link="true"></login-footer>
    </form>
</template>

<script>
    import LoginFooter from "./login-footer.vue";
    import LoginForm from "./login-form";

    export default {
        props: ['lastUsername', 'error', 'askForTargetCloud'],
        components: {LoginForm, LoginFooter},
        data() {
            return {
                ownCloud: false,
                authenticating: false,
                displayError: false,
            };
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

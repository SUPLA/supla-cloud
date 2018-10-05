<template>
    <form @submit="authenticating = true"
        action="/oauth/v2/auth_login"
        ref="loginForm"
        method="post"
        v-title="$t('Login')"
        class="login-oauth-form">
        <login-form :authenticating="authenticating"
            :error="error"
            :intitial-username="lastUsername">
            <div class="form-group">
                <div class="row supla-cloud-chooser text-center">
                    <div class="col-sm-6">
                        <input id="server-official"
                            type="radio"
                            v-model="cloudMode"
                            class="radio"
                            value="official">
                        <label for="server-official">{{ $t('supla.org account') }}</label>
                    </div>
                    <div class="col-sm-6">
                        <input id="server-custom"
                            type="radio"
                            v-model="cloudMode"
                            class="radio"
                            value="custom">
                        <label for="server-custom">{{ $t('another instance') }}</label>
                    </div>
                </div>
            </div>
            <div class="form-group form-group-lg"
                v-if="cloudMode == 'custom'">
                <span class="input-group">
                    <span class="input-group-addon">
                        <span class="pe-7s-global"></span>
                    </span>
                    <input type="text"
                        required
                        autocorrect="off"
                        autocapitalize="none"
                        :placeholder="$t('Your Cloud address')"
                        v-model="cloudAddress"
                        name="cloudAddress"
                        class="form-control">
                </span>
            </div>
        </login-form>
        <login-footer remind-password-link="true"></login-footer>
    </form>
</template>

<script>
    import LoginFooter from "./login-footer.vue";
    import LoginForm from "./login-form";

    export default {
        props: ['lastUsername', 'error'],
        components: {LoginForm, LoginFooter},
        data() {
            return {
                cloudMode: 'official',
                authenticating: false,
                displayError: false,
            };
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .supla-cloud-chooser {
        input[type=radio] {
            display: none;
        }
        input[type=radio] + label::before {
            content: '';
            display: inline-block;
            border: 1px solid $supla-grey-dark;
            border-radius: 50%;
            margin: 0 0.5em;
            vertical-align: middle;
        }
        input[type=radio]:checked + label::before {
            background-color: $supla-green;
        }
        .radio + label::before {
            width: 1.5em;
            height: 1.5em;
        }
        label {
            font-weight: normal;
        }
    }
</style>

<template>
    <form @submit.prevent="login()"
        ref="loginForm"
        method="post"
        v-title="$t('Login')">
        <login-form v-model="user"
            :authenticating="authenticating"
            :error="displayError"></login-form>
        <login-footer remind-password-link="true"></login-footer>
    </form>
</template>

<script>
    import LoginForm from "./login-form.vue";
    import LoginFooter from "./login-footer.vue";
    import {errorNotification} from "../common/notifier";

    export default {
        components: {LoginFooter, LoginForm},
        data() {
            return {
                authenticating: false,
                user: undefined,
                displayError: false,
            };
        },
        methods: {
            login() {
                if (!this.authenticating) {
                    this.authenticating = true;
                    this.displayError = false;
                    this.$user.authenticate(this.user.username, this.user.password)
                        .then(() => this.$router.push(this.$router.currentRoute.query.target || '/'))
                        .catch((error) => {
                            if (error.status == 401) {
                                this.displayError = 'error';
                            } else if (error.status == 429) {
                                this.displayError = 'locked';
                            }
                            else {
                                console.warn(error); // eslint-disable-line no-console
                                errorNotification(this.$t('Information'), this.$t('Sign in temporarily unavailable. Please try again later.'));
                            }
                            this.authenticating = false;
                        });
                }
            }
        }
    };
</script>

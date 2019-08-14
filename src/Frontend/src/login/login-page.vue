<template>
    <form @submit.prevent="login()"
        class="login-form-container"
        ref="loginForm"
        method="post"
        v-title="$t('Login')">
        <login-form v-model="user"
            :authenticating="authenticating"
            :error="displayError"></login-form>
    </form>
</template>

<script>
    import LoginForm from "./login-form.vue";
    import {errorNotification} from "../common/notifier";

    export default {
        components: {LoginForm},
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
                        .then(userData => {
                            if (userData) {
                                if (userData.locale) {
                                    this.$setLocale(userData.locale);
                                } else {
                                    this.$updateUserLocale(this.$i18n.locale);
                                }
                            }
                        })
                        .then(() => this.$router.push(this.$router.currentRoute.query.target || '/'))
                        .catch((error) => {
                            if (error.status == 401) {
                                this.displayError = 'error';
                            } else if (error.status == 409) {
                                this.displayError = 'disabled';
                            } else if (error.status == 429) {
                                this.displayError = 'locked';
                            } else {
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

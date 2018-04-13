<template>
    <div>
        <loading-cover :loading="!tokenValid"></loading-cover>
        <form class="recovery-form"
            @submit.prevent="reset()"
            v-if="tokenValid">
            <h1 v-title>{{ $t('Password Reset') }}</h1>
            <div class="form-group">
                <label>{{ $t('Enter new password') }}</label>
                <input type="password"
                    class="form-control"
                    v-model="password">
            </div>
            <div class="form-group">
                <label>{{ $t('Repeat password') }}</label>
                <input type="password"
                    class="form-control"
                    v-model="passwordConfirm">
            </div>
            <div>
                <button type="submit"
                    class="btn btn-black"
                    :disabled="loading">
                    <button-loading-dots v-if="loading"></button-loading-dots>
                    <span v-else>{{ $t('RESET') }}</span>
                </button>
            </div>
        </form>
        <login-footer></login-footer>
    </div>
</template>

<script>
    import LoginFooter from "./login-footer.vue";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {errorNotification, successNotification} from "../common/notifier";

    export default {
        components: {LoginFooter, ButtonLoadingDots},
        props: ['token'],
        data() {
            return {
                loading: false,
                password: '',
                passwordConfirm: '',
                tokenValid: false
            };
        },
        mounted() {
            this.$http.head('forgotten-password/' + this.token, {skipErrorHandler: [400]})
                .then(() => this.tokenValid = true)
                .catch(() => {
                    errorNotification('Error', 'Token does not exist', this);
                    this.$router.push({name: 'login'});
                });
        },
        methods: {
            reset() {
                if (this.password.length < 8) {
                    return errorNotification('Error', 'The password should be 8 or more characters.', this);
                } else if (this.password != this.passwordConfirm) {
                    return errorNotification('Error', 'The password and its confirm are not the same.', this);
                }
                this.loading = true;
                this.$http.put('forgotten-password/' + this.token, {password: this.password})
                    .then(() => {
                        successNotification('Success', 'Password has been changed!', this);
                        this.$router.push({name: 'login'});
                    })
                    .finally(() => this.loading = false);
            }
        }
    };
</script>

<style scoped
    lang="scss">
    @import "../styles/variables";

    .recovery-form {
        max-width: 400px;
        position: absolute;
        top: 50%;
        left: 50%;
        margin: 0 auto;
        margin-left: -200px;
        transform: translateY(-50%);
        @media (max-width: 500px) {
            position: static;
            width: 90%;
            margin: 10px;
            transform: none;
            height: auto;
        }
        @media (max-height: 500px) {
            position: static;
            width: 90%;
            margin: 10px;
            transform: none;
            height: auto;
        }
        input {
            border-color: $supla-black;
            color: $supla-black;
        }
    }
</style>

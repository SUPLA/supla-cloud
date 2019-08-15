<template>
    <loading-cover :loading="!tokenValid">
        <form class="centered-form-container"
            @submit.prevent="reset()"
            v-if="tokenValid">
            <div class="recovery-form centered-form">
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
            </div>
        </form>
    </loading-cover>
</template>

<script>
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";
    import {errorNotification, successNotification} from "../common/notifier";

    export default {
        components: {ButtonLoadingDots},
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
                    errorNotification(this.$t('Error'), this.$t('Token does not exist'));
                    this.$router.push({name: 'login'});
                });
        },
        methods: {
            reset() {
                if (this.password.length < 8) {
                    return errorNotification(this.$t('Error'), this.$t('The password should be 8 or more characters.'));
                } else if (this.password != this.passwordConfirm) {
                    return errorNotification(this.$t('Error'), this.$t('The password and its confirm are not the same.'));
                }
                this.loading = true;
                this.$http.put('forgotten-password/' + this.token, {password: this.password})
                    .then(() => {
                        successNotification(this.$t('Success'), this.$t('Password has been changed'));
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
        input {
            border-color: $supla-black;
            color: $supla-black;
        }
    }
</style>

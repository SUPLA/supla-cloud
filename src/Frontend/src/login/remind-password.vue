<template>
    <div>
        <div class="bg"></div>

        <form class="recovery-form"
            @submit.prevent="remind()">
            <h1>{{ $t('Password Reset') }}</h1>
            <div class="form-group form-group-lg">
                <input autocomplete="off"
                    class="form-control"
                    required
                    v-model="email"
                    type="email"
                    :placeholder="$t('Enter your email address')">
            </div>
            <div class="form-group text-right">
                <button type="submit"
                    class="btn btn-black">
                    <span v-if="!loading">{{ $t('RESET') }}</span>
                    <button-loading-dots v-else></button-loading-dots>
                </button>
            </div>

            <p v-if="sent"><strong>{{ $t('Check your email box') }}</strong></p>
            <p v-else-if="sentProblem"><strong>{{ $t('Could not reset the password. Try again in a while.') }}</strong></p>

            <div class="col-xs-12 text-center">
                <router-link to="/"
                    class="back">
                    <i class="pe-7s-left-arrow"></i>
                </router-link>
            </div>
        </form>

        <login-footer></login-footer>
    </div>
</template>

<script>
    import LoginFooter from "./login-footer.vue";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";

    export default {
        components: {LoginFooter, ButtonLoadingDots},
        data() {
            return {
                loading: false,
                email: '',
                sent: false,
                sentProblem: false
            };
        },
        methods: {
            remind() {
                if (!this.loading) {
                    this.loading = true;
                    this.sent = this.sentProblem = false;
                    this.$http.post('forgot_passwd', {email: this.email}).then(() => {
                        this.email = '';
                        this.sent = true;
                    }).catch(() => this.sentProblem = true)
                        .finally(() => this.loading = false);
                }
            }
        }
    };
</script>

<style scoped
    lang="scss">
    @import "../styles/variables";

    .bg {
        background: $supla-yellow;
        position: fixed;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: -5;
    }

    .recovery-form {
        $height: 150px;
        max-width: 400px;
        height: $height;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -$height/2;
        margin-left: -200px;
        @media (max-width: 500px) {
            position: static;
            width: 90%;
            margin: 10px;
        }
        @media (max-height: 500px) {
            position: static;
            width: 90%;
            margin: 10px;
        }
        input {
            border-color: $supla-black;
            color: $supla-black;
        }
    }

    .back {
        font-size: 40px;
        color: $supla-black;
    }
</style>

<template>
    <div>
        <form class="recovery-form"
            @submit.prevent="remind()">
            <h1 v-title>{{ $t('Password Reset') }}</h1>
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
            <p v-else-if="sentProblem"><strong>{{ $t('Could not reset the password. Please try again in a while.') }}</strong></p>

            <div class="text-center">
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
                    this.$http.patch('forgotten-password', {email: this.email}).then(() => {
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

    .recovery-form {
        $height: 220px;
        max-width: 400px;
        height: $height;
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -$height/2;
        margin-left: -200px;
        @media (max-width: 500px) {
            max-width: none;
            position: static;
            width: 90%;
            margin: 10px;
            height: auto;
        }
        @media (max-height: 500px) {
            position: static;
            width: 90%;
            margin: 10px;
            height: auto;
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

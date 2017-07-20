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
    import ButtonLoadingDots from "../common/button-loading-dots.vue";

    export default {
        components: {LoginFooter, ButtonLoadingDots},
        data() {
            return {
                loading: false,
                email: '',
                sent: false,
            };
        },
        methods: {
            remind() {
                if (!this.loading) {
                    this.loading = true;
                    this.$http.post('account/ajax/forgot_passwd', {email: this.email}).then(() => {
                        this.loading = false;
                        this.email = '';
                        this.sent = true;
                    });
                }
            }
        }
    };
</script>

<style scoped
    lang="scss"
    rel="stylesheet/scss">
    .bg {
        background: #FFE838;
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
        @media (max-width: 400px) {
            position: static;
            width: 90%;
            margin: 10px;
        }
        input {
            border-color: black;
            color: black;
        }
    }

    .back {
        font-size: 40px;
        color: black;
    }
</style>

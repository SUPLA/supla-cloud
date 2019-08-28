<template>
    <form class="centered-form-container"
        @submit.prevent="remind()">
        <div class="centered-form recovery-form">
            <h1 v-title>{{ $t('Password Reset') }}</h1>
            <div class="form-group form-group-lg">
                <input autocomplete="off"
                    class="form-control"
                    required
                    v-focus="true"
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
            <p v-else-if="sentProblem"><strong>{{ $t('Could not reset the password. Please try again later.') }}</strong></p>

            <div class="text-center">
                <router-link to="/"
                    class="back">
                    <i class="pe-7s-left-arrow"></i>
                </router-link>
            </div>
        </div>
    </form>
</template>

<script>
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";

    export default {
        components: {ButtonLoadingDots},
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

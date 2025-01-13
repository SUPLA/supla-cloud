<template>
    <span v-if="expirationTimestamp">
        <span v-if="secondsLeft > 0"
            class="text-muted">
            <i18n-t keypath="Your session will expire in {0}." tag="span">
                <span>{{ timeLeft }}</span>
            </i18n-t>
            <a @click="show()"
                v-if="secondsLeft < 300">{{ $t('extend') }}</a>
        </span>
        <modal v-if="showDialog"
            class="modal-confirm"
            :header="$t('Your session is about to expire')"
            :class="['text-center session-countdown-modal', {expiring: secondsLeft < 60}]">
            <p>
                <i18n-t keypath="Your session will expire in {0}."
                    tag="span">
                    <span>{{ timeLeft }}</span>
                </i18n-t>
            </p>
            <div class="form-group">
                <p>{{ $t('Enter your password to prevent automatic logout.') }}</p>
            </div>
            <form @submit.prevent="extendSession()">
                <div class="form-group text-left">
                    <input type="text"
                        name="username"
                        class="form-control"
                        v-model="$user.username"
                        readonly>
                    <label>{{ $t('Your email') }}</label>
                </div>
                <div class="form-group text-left">
                    <input type="password"
                        required
                        v-focus="true"
                        class="form-control"
                        v-model="password"
                        id="extend-password">
                    <label for="extend-password">{{ $t('Password') }}</label>
                </div>
                <button class="hidden"
                    type="submit"></button>
            </form>
            <div class="alert alert-danger"
                v-if="error">{{ $t('Incorrect password') }}
            </div>
            <template #footer>
                <button-loading-dots v-if="loading"></button-loading-dots>
                <div v-else>
                    <div class="pull-left">
                        <a @click="logout()"
                            class="btn btn-default">
                            {{ $t('Sign Out') }}
                        </a>
                    </div>
                    <a @click="cancel()"
                        class="cancel">
                        <i class="pe-7s-close"></i>
                    </a>
                    <a class="confirm"
                        @click="extendSession()">
                        <i class="pe-7s-check"></i>
                    </a>
                </div>
            </template>
        </modal>
    </span>
</template>

<script>
    import Vue from "vue";
    import AppState from "../../router/app-state";
    import {DateTime} from "luxon";

    export default {
        data() {
            return {
                showDialog: false,
                loading: false,
                password: undefined,
                error: false,
                expirationTimestamp: undefined,
                interval: undefined,
                secondsLeft: undefined,
            };
        },
        mounted() {
            this.synchronizeExpirationTime();
            this.interval = setInterval(() => this.countdown(), 1000);
        },
        computed: {
            timeLeft() {
                if (this.secondsLeft > 0) {
                    const minutes = Math.floor(this.secondsLeft / 60);
                    const seconds = this.secondsLeft % 60;
                    return (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
                } else {
                    return '';
                }
            }
        },
        methods: {
            synchronizeExpirationTime() {
                const expirationTime = this.$localStorage.get('_token_expiration');
                if (expirationTime) {
                    const timestamp = DateTime.fromISO(expirationTime).toSeconds();
                    if (timestamp > (new Date().getTime() / 1000)) {
                        this.expirationTimestamp = timestamp;
                        this.countdown();
                    }
                }
            },
            cancel() {
                this.showDialog = false;
                this.password = undefined;
                this.error = false;
            },
            show() {
                this.showDialog = true;
                Vue.nextTick(() => this.countdown());
            },
            extendSession() {
                this.loading = true;
                this.error = false;
                this.$user.authenticate(this.$user.username, this.password)
                    .then(() => this.synchronizeExpirationTime())
                    .then(() => this.cancel())
                    .catch(() => this.error = true)
                    .finally(() => this.loading = false);
            },
            countdown() {
                if (this.expirationTimestamp) {
                    this.secondsLeft = this.expirationTimestamp - Math.floor(new Date().getTime() / 1000);
                    if (this.secondsLeft < 60 && !this.showDialog) {
                        this.show();
                    }
                    if (this.secondsLeft <= 0) {
                        AppState.addTask('sessionExpired', true);
                        this.logout();
                    }
                }
            },
            logout() {
                this.cancel();
                clearInterval(this.interval);
                this.expirationTimestamp = undefined;
                document.getElementById('logoutButton').dispatchEvent(new MouseEvent('click'));
            }
        },
        beforeDestroy() {
            if (this.interval) {
                clearInterval(this.interval);
            }
        }
    };
</script>

<style lang="scss">
    @import "../../styles/variables";
    @import "../../styles/mixins";

    .session-countdown-modal {
        &.expiring {
            .cancel {
                display: none;
            }
        }
    }
</style>

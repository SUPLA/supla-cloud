<template>
    <div class="account-page"
        v-title="$t('Account')">
        <animated-svg id="user-account-bg"
            :file="'assets/img/user-account-bg.svg' | withBaseUrl(false)"></animated-svg>
        <div :class="'user-account-container ' + (animationFinished ? 'animation-finished' : '')">
            <loading-cover :loading="!user">
                <span class="supla-version">supla cloud {{ frontendVersion }}</span>
                <transition name="fade">
                    <div class="user-account"
                        v-if="user">
                        <h1>{{ user.email }}</h1>
                        <dl class="no-margin">
                            <dt>{{ $t('Server address') }}</dt>
                            <dd>
                                {{ suplaServerHost }}
                            </dd>
                        </dl>
                        <dl>
                            <dt>{{ $t('Timezone') }}</dt>
                            <dd>
                                <timezone-picker :timezone="user.timezone"></timezone-picker>
                            </dd>
                        </dl>
                        <div class="form-group text-center">
                            <a class="btn btn-default"
                                @click="changingNotifications = true">{{ $t('E-mail notifications') }}</a>
                            <a class="btn btn-default"
                                @click="showingLimits = true">{{ $t('Show my limits') }}</a>
                        </div>
                        <div class="text-center">
                            <a class="btn btn-red-outline btn-xs"
                                @click="deletingAccount = true">{{ $t('Delete my account') }}</a>
                        </div>
                    </div>
                </transition>
            </loading-cover>
        </div>
        <div v-if="user">
            <account-opt-out-notifications-modal v-if="changingNotifications"
                @cancel="closeOptOutNotificationsModal()"
                :user="user"></account-opt-out-notifications-modal>
            <account-limits-modal v-if="showingLimits"
                @confirm="showingLimits = false"
                :user="user"></account-limits-modal>
            <account-delete-modal v-if="deletingAccount"
                @cancel="deletingAccount = false"
                :user="user"></account-delete-modal>
        </div>
    </div>
</template>

<script>
    import AnimatedSvg from "./animated-svg";
    import TimezonePicker from "./timezone-picker";
    import AccountOptOutNotificationsModal from "./account-opt-out-notifications-modal";
    import AccountDeleteModal from "./account-delete-modal";
    import AccountLimitsModal from "./account-limits-modal";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        components: {
            AccountLimitsModal,
            AccountDeleteModal,
            TimezonePicker,
            AnimatedSvg,
            AccountOptOutNotificationsModal,
        },
        data() {
            return {
                user: undefined,
                animationFinished: false,
                deletingAccount: false,
                showingLimits: false,
                changingNotifications: false,
            };
        },
        mounted() {
            setTimeout(() => this.animationFinished = true, 2000);
            this.$http.get('users/current').then(response => {
                this.user = response.body;
            });
            if (this.$route.query.optOutNotification) {
                this.changingNotifications = true;
            }
        },
        methods: {
            closeOptOutNotificationsModal() {
                this.changingNotifications = false;
                if (this.$route.query.optOutNotification) {
                    this.$router.push({optOutNotification: undefined});
                }
            }
        },
        computed: {
            suplaServerHost() {
                return this.frontendConfig.suplaUrl.replace(/https?:\/\//, '');
            },
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config', 'frontendVersion': 'frontendVersion'}),
        },
    };
</script>

<style lang="scss">
    @import '../styles/mixins';
    @import '../styles/variables';

    ._account_view {
        background-color: $supla-green;
    }

    .account-page {
        margin-top: -23px;
    }

    #user-account-bg {
        display: block;
        width: 610px;
        height: 650px;
        position: absolute;
        left: 50%;
        margin-left: -305px;
    }

    .user-account-container {
        width: 558px;
        padding-top: 222px;
        margin: 0 auto;
        position: relative;
        color: $supla-black;
        .supla-version {
            background: $supla-black;
            border-radius: 15px;
            padding: 2px 10px;
            display: inline-block;
            font-family: 'Quicksand', sans-serif;
            font-size: 15px;
            font-weight: 400;
            color: $supla-white;
        }
        .user-account {
            margin-top: 14px;
            border-radius: 3px;
            padding: 15px;
            height: 366px;
            background: $supla-white;
            h1 {
                margin-top: 0;
                text-transform: none;
                border-bottom: solid 1px rgba(0, 0, 0, .2);
                text-overflow: ellipsis;
                white-space: nowrap;
                overflow: hidden;
                font-size: 36px;
                line-height: 64px;
            }
            dt {
                float: left;
                clear: left;
                width: 170px;
                line-height: 25px;
                font-weight: 400;
            }
            dd {
                margin: 0 0 0 180px;
                line-height: 25px;
                color: $supla-green
            }
        }
    }

    @include on-xs-and-down {
        .account-page {
            margin-top: 0;
        }
        #user-account-bg {
            display: none;
        }
        .user-account-container {
            padding-top: 0;
            width: 95%;
            .user-account {
                height: auto;
            }
        }
    }

    @include on-xs-and-up {
        .user-account-container {
            opacity: 0;
            transition: opacity .3s;
            .user-account {
                opacity: 0;
                transition: opacity .3s;
                transition-delay: .4s;
            }
            &.animation-finished {
                opacity: 1;
                .user-account {
                    opacity: 1;
                }
            }
        }
    }
</style>

<template>
    <div class="account-page">
        <animated-svg id="user-account-bg"
            :file="'assets/img/user-account-bg.svg' | withBaseUrl"></animated-svg>
        <div :class="'user-account-container ' + (animationFinished ? 'animation-finished' : '')">
            <loading-cover :loading="!user">
                <span class="supla-version">supla cloud {{ version }}</span>
                <transition name="fade">
                    <div class="user-account"
                        v-if="user">
                        <h1>{{ user.email }}</h1>
                        <dl>
                            <dt>{{ $t('Previous login') }}</dt>
                            <dd>
                                <strong v-if="user.lastLogin">{{ user.currentlogin | moment('LLL') }}</strong>
                                <strong v-else>{{ user.lastlogin | moment('LLL') }}</strong>
                            </dd>
                            <dt>{{ $t('From IP') }}</dt>
                            <dd>
                                <strong v-if="user.lastLogin">{{ user.currentipv4 | intToIp}}</strong>
                                <strong v-else>{{ user.lastipv4 | intToIp}}</strong>
                            </dd>
                            <dt>{{ $t('Timezone') }}</dt>
                            <dd>
                                <timezone-picker :timezone="user.timezone"></timezone-picker>
                            </dd>
                        </dl>
                        <a class="btn btn-default"
                            @click="changingPassword = true">{{ $t('Change Password') }}</a>
                        <account-password-change-modal v-if="changingPassword"
                            @cancel="changingPassword = false"
                            :user="user"></account-password-change-modal>
                    </div>
                </transition>
            </loading-cover>
        </div>
    </div>
</template>

<script type="text/babel">
    import AnimatedSvg from "./animated-svg";
    import TimezonePicker from "./timezone-picker";
    import AccountPasswordChangeModal from "./account-password-change-modal";

    export default {
        components: {
            AccountPasswordChangeModal,
            TimezonePicker,
            AnimatedSvg
        },
        data() {
            return {
                user: undefined,
                animationFinished: false,
                changingPassword: false,
                version: VERSION, // eslint-disable-line no-undef
            };
        },
        mounted() {
            setTimeout(() => this.animationFinished = true, 2000);
            this.$http.get('users/current').then(response => {
                this.user = response.body;
            });
        },
        computed: {}
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
        width: 600px;
        height: 589px;
        position: absolute;
        left: 50%;
        margin-left: -300px;
    }

    .user-account-container {
        width: 550px;
        padding-top: 217px;
        margin: 0 auto;
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
            height: 306px;
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

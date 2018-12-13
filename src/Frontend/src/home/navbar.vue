<template>
    <nav class="navbar navbar-default navbar-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button"
                    class="navbar-toggle collapsed"
                    data-toggle="collapse"
                    data-target="#supla-navbar"
                    aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <router-link to="/"
                    class="navbar-brand">
                    <supla-logo></supla-logo>
                    supla
                </router-link>
            </div>

            <div class="collapse navbar-collapse"
                id="supla-navbar">
                <ul class="nav navbar-nav">
                    <router-link tag="li"
                        :class="{'active': subIsActive(['/devices', '/channels'])}"
                        to="/me">
                        <a>
                            <i class="hidden-sm hidden-xs pe-7s-plug"></i>
                            {{ $t('My SUPLA') }}
                        </a>
                    </router-link>
                    <router-link tag="li"
                        to="/smartphones">
                        <a>
                            <i class="hidden-sm hidden-xs pe-7s-phone"></i>
                            {{ $t('Smartphones') }}
                        </a>
                    </router-link>
                    <router-link tag="li"
                        to="/locations">
                        <a>
                            <i class="hidden-sm hidden-xs pe-7s-home"></i>
                            {{ $t('Locations') }}
                        </a>
                    </router-link>
                    <router-link tag="li"
                        to="/access-identifiers">
                        <a>
                            <i class="hidden-sm hidden-xs pe-7s-key"></i>
                            {{ $t('Access Identifiers') }}
                        </a>
                    </router-link>

                    <li class="dropdown"
                        :class="{'active': subIsActive(['/schedules', '/channel-groups'])}">
                        <a class="dropdown-toggle"
                            data-toggle="dropdown">
                            <i class="hidden-sm hidden-xs pe-7s-config"></i>
                            {{ $t('Automation') }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <router-link tag="li"
                                to="/schedules">
                                <a>
                                    <i class="hidden-sm hidden-xs pe-7s-clock"></i>
                                    {{ $t('Schedules') }}
                                </a>
                            </router-link>
                            <router-link tag="li"
                                to="/channel-groups">
                                <a>
                                    <i class="hidden-sm hidden-xs pe-7s-keypad"></i>
                                    {{ $t('Channel groups') }}
                                </a>
                            </router-link>
                            <router-link tag="li"
                                :to="{name: 'directLinks'}">
                                <a>
                                    <i class="hidden-sm hidden-xs pe-7s-link"></i>
                                    {{ $t('Direct links') }}
                                </a>
                            </router-link>
                        </ul>
                    </li>
                    <li class="dropdown account-dropdown">
                        <a class="dropdown-toggle"
                            data-toggle="dropdown">
                            <i class="hidden-sm hidden-xs pe-7s-user"></i>
                            {{ $t('Account') }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <router-link tag="li"
                                to="/account">
                                <a class="my-account">
                                    <span class="username"
                                        v-if="$user">{{ $user.username }}</span>
                                    {{ $t('Go to your account') }}
                                </a>
                            </router-link>
                            <li class="divider"></li>
                            <li class="flags"
                                v-for="(localesRow, index) in locales"
                                :key="index">
                                <a v-for="locale in localesRow"
                                    :key="locale.value"
                                    :title="locale.text"
                                    :class="($i18n.locale == locale.value ? 'active' : '')"
                                    class="link">
                                    <img :src="`assets/img/flags/${locale.value}.svg` | withBaseUrl"
                                        @click="setLocale(locale.value)">
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li class="bottom">
                                <div class="btn-group btn-group-justified">
                                    <router-link :to="{name: 'authorized-oauth-apps'}"
                                        class="btn btn-default btn-wrapped">
                                        {{ $t('Integrations') }}
                                    </router-link>
                                    <a class="btn btn-default btn-wrapped"
                                        id="logoutButton"
                                        @click="logout()">
                                        {{ $t('Sign Out') }}
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
    import SuplaLogo from "./supla-logo";
    import Vue from "vue";

    export default {
        components: {SuplaLogo},
        computed: {
            locales() {
                let third = Math.ceil(Vue.config.availableLanguages.length / 3);
                return [
                    Vue.config.availableLanguages.slice(0, third),
                    Vue.config.availableLanguages.slice(third, third * 2),
                    Vue.config.availableLanguages.slice(third * 2, third * 3),
                ];
            }
        },
        methods: {
            setLocale(lang) {
                this.$setLocale(lang);
            },
            subIsActive(input) {
                const paths = Array.isArray(input) ? input : [input];
                return paths.some(path => {
                    return this.$route.path.indexOf(path) === 0;
                });
            },
            logout() {
                this.$user.forget();
                this.$router.push({name: 'login'});
            }
        }
    };
</script>

<style lang="scss">
    @import '../styles/mixins.scss';
    @import '../styles/variables.scss';

    nav.navbar-top {
        background: $supla-white;
        @media only screen and (min-width: 768px) {
            .navbar-collapse {
                display: flex !important;

                > .nav {
                    flex: 1;
                    display: flex;

                    > li {
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                    }
                }
            }
        }

        .navbar-brand {
            width: 160px;
            font-size: 2em;
            color: $supla-black;
            font-family: $supla-font-special;

            svg {
                width: 45px;
                height: 45px;
                vertical-align: text-top;
            }

            @media only screen and (min-width: 992px) {
                padding-top: 20px;
            }
            @include on-xs-and-down {
                padding-top: 6px;
                svg {
                    width: 40px;
                    height: 40px;
                }
            }
        }

        .nav > li {
            transition: all .3s;

            > a {
                text-align: center;
                font-size: 12px;

                i {
                    font-size: 2em;
                    display: block;
                    margin: 0 auto;
                    margin-bottom: 10px;
                }

                &:hover, &:focus {
                    color: $supla-green;
                }
            }
        }

        .nav > li.active, .nav > li.open, .dropdown-menu > li.active {
            background: $supla-green;
            border-bottom-color: $supla-green;

            > a, > a:hover, > a:focus {
                border-bottom-color: $supla-green;
                background: transparent;
                color: $supla-white;
            }

            @include on-xs-and-down {
                background: transparent;
                > a, > a:hover, > a:focus {
                    background: $supla-green;
                }
            }
        }

        .account-dropdown {
            .dropdown-menu {
                border-top-left-radius: 4px;
                min-width: 240px;
            }

            &, a {
                text-align: center;
            }

            .username {
                display: block;
                text-align: center;
                font-size: 1.3em;
                font-family: $supla-font-special;
            }

            .flags {
                text-align: center;

                .link {
                    display: inline-block;
                    padding: 3px;

                    &.active {
                        img {
                            border-color: $supla-green;
                        }
                    }
                }

                img {
                    display: block;
                    width: 36px;
                    height: 36px;
                    border: solid 2px $supla-grey-light;
                    border-radius: 100%;
                    transition: all .2s ease-in-out;
                }
            }

            .btn-group {
                margin: 0;
                padding: 0;

                .btn {
                    border: 0;
                }
            }
        }
    }
</style>

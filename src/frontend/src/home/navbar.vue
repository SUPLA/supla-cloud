<template>
    <nav class="navbar navbar-default navbar-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button"
                    :class="['navbar-toggle', {collapsed}]"
                    @click="collapsed = !collapsed">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <router-link :to="{name: 'home'}"
                    class="navbar-brand">
                    <supla-logo></supla-logo>
                    supla
                </router-link>
            </div>

            <div :class="['collapse navbar-collapse', {in: !collapsed}]">
                <ul class="nav navbar-nav">
                    <li>
                        <router-link
                            active-class="link-active"
                            exact-active-class="active"
                            :class="{'active': subIsActive(['/devices', '/channels'])}"
                            :to="{name: 'me'}">
                            <i class="hidden-sm hidden-xs pe-7s-plug"></i>
                            {{ $t('My SUPLA') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/smartphones">
                            <i class="hidden-sm hidden-xs pe-7s-phone"></i>
                            {{ $t('Smartphones') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/locations">
                            <i class="hidden-sm hidden-xs pe-7s-home"></i>
                            {{ $t('Locations') }}
                        </router-link>
                    </li>
                    <li>
                        <router-link to="/access-identifiers">
                            <i class="hidden-sm hidden-xs pe-7s-key"></i>
                            {{ $t('Access Identifiers') }}
                        </router-link>
                    </li>

                    <li class="dropdown"
                        :class="{'active': subIsActive(['/schedules', '/channel-groups', '/scenes', '/direct-links', '/reactions'])}">
                        <a class="dropdown-toggle"
                            data-toggle="dropdown">
                            <i class="hidden-sm hidden-xs pe-7s-config"></i>
                            {{ $t('Automation') }}
                            <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <router-link :to="{name: 'schedules'}">
                                    <i class="hidden-sm hidden-xs pe-7s-clock mr-1"></i>
                                    {{ $t('Schedules') }}
                                </router-link>
                            </li>
                            <li>
                                <router-link :to="{name: 'channelGroups'}">
                                    <i class="hidden-sm hidden-xs pe-7s-keypad mr-1"></i>
                                    {{ $t('Channel groups') }}
                                </router-link>
                            </li>
                            <li>
                                <router-link :to="{name: 'directLinks'}">
                                    <i class="hidden-sm hidden-xs pe-7s-link mr-1"></i>
                                    {{ $t('Direct links') }}
                                </router-link>
                            </li>
                            <li>
                                <router-link :to="{name: 'scenes'}">
                                    <i class="hidden-sm hidden-xs supla-icon supla-icon-scene mr-1"></i>
                                    {{ $t('Scenes') }}
                                </router-link>
                            </li>
                            <li>
                                <router-link :to="{name: 'reactions'}">
                                    <i class="hidden-sm hidden-xs pe-7s-arc mr-1"></i>
                                    {{ $t('Reactions') }}
                                </router-link>
                            </li>
                            <li role="separator"
                                class="divider"></li>
                            <li>
                                <a href="https://cloud.supla.org/apps"
                                    target="_blank">
                                    <i class="hidden-sm hidden-xs pe-7s-science"></i>
                                    {{ $t('Applications') }}
                                </a>
                            </li>
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
                            <li>
                                <router-link to="/account" class="text-center">
                                    <span class="username" v-if="currentUserStore.username">{{ currentUserStore.username }}</span>
                                    {{ $t('Go to your account') }}
                                </router-link>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <router-link :to="{name: 'integrations.myOauthApps'}">
                                    <fa icon="puzzle-piece" class="mr-1" fixed-width/>
                                    {{ $t('Integrations') }}
                                </router-link>
                            </li>
                            <li>
                                <router-link :to="{name: 'safety.log'}">
                                    <fa icon="shield-halved" class="mr-1" fixed-width/>
                                    {{ $t('Security') }}
                                </router-link>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a id="logoutButton" @click="logout()">
                                    <fa icon="sign-out" fixed-width/>
                                    {{ $t('Sign Out') }}
                                </a>
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
    import {mapStores} from "pinia";
    import {useCurrentUserStore} from "@/stores/current-user-store";

    export default {
        components: {SuplaLogo},
        data() {
            return {
                collapsed: true,
            };
        },
        methods: {
            subIsActive(input) {
                const paths = Array.isArray(input) ? input : [input];
                return paths.some(path => {
                    return this.$route.path.indexOf(path) === 0;
                });
            },
            logout() {
                this.$http.post('logout', undefined, {skipErrorHandler: true});
                this.currentUserStore.forget();
                this.$router.push({name: 'login'});
            }
        },
        computed: {
            ...mapStores(useCurrentUserStore),
        },
        watch: {
            $route() {
                this.collapsed = true;
            }
        }
    };
</script>

<style lang="scss">
    @import '../styles/mixins.scss';
    @import '../styles/variables.scss';

    nav.navbar-top {
        background: $supla-white;
        border: 0;
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
                width: 100%;

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

        .nav {
            > li > a.active, > li.open > a, .dropdown-menu > li > a.active, li.dropdown.active > a {
                background: $supla-green;
                border-bottom-color: $supla-green;
                color: $supla-white;

                @include on-xs-and-down {
                    background: transparent;
                    &, &:hover, &:focus {
                        background: $supla-green;
                    }
                }
            }
        }

        .account-dropdown {
            .dropdown-menu {
                border-top-left-radius: 4px;
                min-width: 240px;
            }

            .username {
                display: block;
                text-align: center;
                font-size: 1.3em;
                font-family: $supla-font-special;
            }
        }

        .dropdown-menu li > a {
            white-space: nowrap;
        }
    }

    .green nav.navbar-top {
        background: $supla-green;
        .navbar-brand {
            color: $supla-white;
            fill: $supla-white;
        }
        .nav > li > a {
            color: $supla-white;
        }
        li > a.active, li.open > a, .dropdown-menu > li > a.active, li.dropdown.active > a {
            background: darken($supla-green, 10%);
        }
    }

    .red nav.navbar-top {
        background: $supla-red;
        .navbar-brand {
            color: $supla-white;
            fill: $supla-white;
        }
        .nav > li > a {
            color: $supla-white;
        }
        li > a.active, li.open > a, .dropdown-menu > li > a.active, li.dropdown.active > a {
            background: darken($supla-red, 10%);
        }
    }

    .yellow nav.navbar-top {
        background: $supla-yellow;
        .navbar-brand {
            color: $supla-black;
            fill: $supla-black;
        }
        .nav > li > a {
            color: $supla-black;
        }
        li > a.active, li.open > a, .dropdown-menu > li > a.active, li.dropdown.active > a {
            background: darken($supla-yellow, 15%);
        }
    }
</style>

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
                                        v-if="user">{{ user.email }}</span>
                                    {{ $t('Go to your account') }}
                                </a>
                            </router-link>
                            <li class="divider"></li>
                            <li class="flags">
                                <router-link :to="{query: { lang: flag }}"
                                    v-for="flag in ['en', 'pl', 'ru', 'de']"
                                    :key="flag"
                                    :class="(currentLocale == flag ? 'active' : '')"
                                    @click.native="reloadPage()">
                                    <img :src="`assets/img/flags/${flag}.svg` | withBaseUrl">
                                </router-link>
                            </li>
                            <li class="flags">
                                <router-link :to="{query: { lang: flag }}"
                                    v-for="flag in ['it', 'pt', 'es', 'fr']"
                                    :key="flag"
                                    :class="(currentLocale == flag ? 'active' : '')"
                                    @click.native="reloadPage()">
                                    <img :src="`assets/img/flags/${flag}.svg` | withBaseUrl">
                                </router-link>
                            </li>
                            <li class="divider"></li>
                            <li class="bottom">
                                <div class="btn-group btn-group-justified">
                                    <router-link to="/api"
                                        class="btn btn-default">
                                        {{ $t('RESTful API') }}
                                    </router-link>
                                    <a class="btn btn-default"
                                        :href="'/auth/logout' | withBaseUrl">
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
        data() {
            return {
                user: undefined
            };
        },
        mounted() {
            this.$http.get('users/current').then(response => {
                this.user = response.data;
            });
        },
        methods: {
            reloadPage() {
                window.location.assign(window.location.toString());
            },
            subIsActive(input) {
                const paths = Array.isArray(input) ? input : [input];
                return paths.some(path => {
                    return this.$route.path.indexOf(path) === 0;
                });
            }
        },
        computed: {
            currentLocale() {
                return Vue.config.external.locale;
            }
        }
    };
</script>

<style lang="scss">

</style>

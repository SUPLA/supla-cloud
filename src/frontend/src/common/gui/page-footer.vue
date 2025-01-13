<template>
    <div class="page-footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-3">
                    <language-selector/>
                </div>
                <div class="col-sm-6 text-center">
                    <span class="text-muted">SUPLA Cloud {{ versionSignature }}</span>
                    <a class="brand nav-link"
                        href="https://www.supla.org">www.supla.org</a>
                </div>
                <div class="col-sm-3 text-right">
                    <span v-if="username">
                        <session-countdown></session-countdown>
                    </span>
                    <div v-else-if="isLoginPage && !frontendConfigStore.config.maintenanceMode">
                        <a v-if="showRegisterCloud"
                            class="brand nav-link"
                            :href="`https://cloud.supla.org/register-cloud?domain=${domain}`">
                            {{ $t('Register your SUPLA Cloud') }}
                        </a>
                    </div>
                    <a v-else
                        :href="$router.resolve({name: 'login'}).href"
                        class="nav-link">
                        {{ $t('Sign In') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import LanguageSelector from "./language-selector.vue";
    import SessionCountdown from "./session-countdown";
    import {mapStores} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        props: ['username'],
        components: {LanguageSelector, SessionCountdown},
        computed: {
            versionSignature() {
                return this.frontendConfigStore.backendAndFrontendVersionMatches ?
                    this.frontendConfigStore.frontendVersion
                    : `${this.frontendConfigStore.frontendVersion} / ${this.frontendConfigStore.cloudVersion}`;
            },
            isLoginPage() {
                return this.$route.path.indexOf('/login') === 0 || this.$route.path.indexOf('/oauth-authorize') === 0;
            },
            domain() {
                return this.frontendConfigStore.config.suplaUrl.replace(/https?:\/\//, '');
            },
            showRegisterCloud() {
                return !this.frontendConfigStore.config.actAsBrokerCloud && !this.frontendConfigStore.config.isCloudRegistered
                    && this.frontendConfigStore.config.suplaUrl.indexOf('https') === 0
                    && this.domain.indexOf('localhost:') !== 0
                    && this.domain !== 'localhost';
            },
            ...mapStores(useFrontendConfigStore),
        }
    };
</script>

<style lang="scss">
    @import "../../styles/variables";
    @import "../../styles/mixins";

    .page-footer {
        padding-top: 10px;
        a {
            font-weight: 400;
            color: $supla-black;
            border-radius: 3px;
            padding: 7px 9px;
            &:hover {
                background: rgba(0, 2, 4, 0.08);
                color: $supla-black;
            }
        }
        @include on-xs-and-down {
            &, .text-right {
                text-align: center;
            }
            .nav-link {
                display: inline-block;
            }
        }
    }

    .green, .blue, .warning {
        .page-footer {
            &, & a, & select {
                color: $supla-white !important;
            }
        }
    }

    @media (min-height: 500px) and (min-width: 768px) {
        $footerHeight: 60px;
        html, body, #vue-container {
            height: 100%;
        }
        .page-content {
            min-height: 100%;
            height: auto;
            margin-bottom: -$footerHeight;
            padding: 0 0 $footerHeight;
        }
        .page-footer {
            height: $footerHeight;
            padding-top: 25px;
        }
    }
</style>

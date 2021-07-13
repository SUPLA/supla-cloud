<template>
    <div class="page-footer">
        <div class="container-fluid">
            <div class="footer-left">
                <language-selector></language-selector>
            </div>
            <div class="footer-right">
                <span v-if="username">
                    <session-countdown></session-countdown>
                </span>
                <router-link v-else-if="isPageActive(['/login', '/oauth-authorize']) && !$frontendConfig.maintenanceMode"
                    to="/forgotten-password"
                    class="brand nav-link">
                    {{ $t('Forgot your password?') }}
                </router-link>
                <a v-else
                    :href="$router.resolve({name: 'login'}).href"
                    class="nav-link">
                    {{ $t('Sign In') }}
                </a>
            </div>
            <div class="footer-center">
                <span class="text-muted">SUPLA Cloud {{ versionSignature }}</span>
                <a class="brand nav-link"
                    href="https://www.supla.org">www.supla.org</a>
            </div>
        </div>
    </div>
</template>

<script>
    import LanguageSelector from "./language-selector.vue";
    import SessionCountdown from "./session-countdown";

    export default {
        props: ['username'],
        components: {LanguageSelector, SessionCountdown},
        data() {
            return {
                versionSignature: ''
            };
        },
        mounted() {
            this.versionSignature =
                this.$backendAndFrontendVersionMatches ?
                    this.$frontendVersion
                    : `${this.$frontendVersion} / ${this.$backendVersion}`;
        },
        methods: {
            isPageActive(input) {
                const paths = Array.isArray(input) ? input : [input];
                return paths.some(path => {
                    return this.$route.path.indexOf(path) === 0;
                });
            },
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
        .footer-center {
            text-align: center;
        }
        @include on-xs-and-up {
            .footer-left {
                float: left;
            }
            .footer-right {
                text-align: right;
                float: right;
            }
        }
        @include on-xs-and-down {
            text-align: center;
            .footer-left, .footer-right {
                padding-bottom: 7px;
            }
        }
    }

    .green .page-footer {
        &, & a, & select {
            color: $supla-white !important;
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

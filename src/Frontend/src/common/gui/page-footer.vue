<template>
    <div class="page-footer">
        <div class="container-fluid">
            <div class="col-sm-4">
                <language-selector></language-selector>
            </div>
            <div class="col-sm-4 text-center">
                <span class="text-muted">SUPLA Cloud {{ version }}</span>
                <a class="brand nav-link"
                    href="https://www.supla.org">www.supla.org</a>
                {{ $user.username }}
            </div>
            <div class="col-sm-4 text-right">
                <span v-if="$user.username">
                    <session-countdown></session-countdown>
                </span>
                <router-link v-else-if="isPageActive(['/login', '/oauth-authorize'])"
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
        </div>
    </div>
</template>

<script>
    import LanguageSelector from "./language-selector.vue";
    import SessionCountdown from "./session-countdown";

    export default {
        components: {LanguageSelector, SessionCountdown},
        data() {
            return {
                version: VERSION, // eslint-disable-line no-undef
            };
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

    @media (min-height: 500px) and (min-width: 768px) {
        $footerHeight: 50px;
        html, body, .vue-container {
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
        }
    }

    .page-footer {
        padding-top: 15px;
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
    }

    .green .page-footer {
        &, & a, & select {
            color: $supla-white !important;
        }
    }
</style>

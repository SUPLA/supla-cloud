<template>
    <div class="login-footer">
        <div class="container-fluid">
            <div class="col-sm-4">
                <language-selector></language-selector>
            </div>
            <div class="col-sm-4 text-center">
                <span class="text-muted">SUPLA Cloud v{{ version }}</span>
                <a class="brand nav-link"
                    href="https://www.supla.org">www.supla.org</a>
            </div>
            <div class="col-sm-4 text-right">
                <router-link v-if="remindPasswordLink"
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

    export default {
        props: ['remindPasswordLink'],
        components: {LanguageSelector},
        data() {
            return {
                version: VERSION, // eslint-disable-line no-undef
            };
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";
    @import "../styles/mixins";

    .login-footer {
        width: 100%;
        position: absolute;
        bottom: 5px;
        z-index: 10;
        @include on-and-down(500px) {
            position: static;
            .col-sm-4 {
                padding-top: 5px;
                text-align: center !important;
            }
        }
        @media (max-height: 500px) {
            position: static;
            .col-sm-4 {
                padding-top: 5px;
                text-align: center !important;
            }
        }
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
</style>

<template>
    <div class="login-footer">
        <div class="container-fluid">
            <div class="col-sm-4">
                <language-selector></language-selector>
            </div>
            <div class="col-sm-4 text-center">
                <a class="brand nav-link"
                    href="https://www.supla.org">www.supla.org</a>
            </div>
            <div class="col-sm-4 text-right">
                <router-link to="/remind"
                    class="brand nav-link">
                    {{ $t('Forgot your password?') }}
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
    import LanguageSelector from "./language-selector.vue";

    export default {
        components: {LanguageSelector}
    };
</script>

<style lang="scss">
    @import "../styles/variables";
    @import "../styles/mixins";

    .login-footer {
        width: 100%;
        position: absolute;
        bottom: 5px;
        @include on-and-down(500px) {
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

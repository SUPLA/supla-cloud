<template>
    <footer>
        <div class="row">
            <div class="col-sm-4 language-selector">
                <language-selector></language-selector>
            </div>
            <div class="col-sm-4 brand-link">
                <a class="brand nav-link"
                    href="https://www.supla.org">www.supla.org</a>
            </div>
            <div class="col-sm-4 sing-in-link">
                <router-link to="/"
                    class="nav-link">{{ $t('Sign In') }}
                </router-link>
            </div>
        </div>
    </footer>
</template>

<script>
    import LanguageSelector from '../login/language-selector.vue';

    export default {
        components: {LanguageSelector}
    };
</script>

<style lang="scss">
    footer {
        margin: 10px;
        color: #fff;
        font-size: 13px;
        line-height: 32px;

        @media screen and (max-width: 899px) {
            position: static;
        }

        @media screen and (min-width: 900px) {
            padding: 0 20px;
            position: fixed;
            bottom: 0;
            left: 0;
            z-index: 9999;
            width: calc(100% - 20px);
        }

        a {
            border: solid 1px transparent;
            border-radius: 3px;
            color: #fff;
            font-weight: 400;
            padding: 7px 9px;

            &:hover {
                background: none;
                border-color: rgba(255, 255, 255, 0.5);
                color: #fff;
            }
        }

        select {
            color: #fff !important;
            border: solid 1px rgba(255, 255, 255, 0.5) !important;
        }
        select::-ms-expand { display: none;}
    }

    .language-selector {
        text-align: left;
    }

    .brand-link {
        text-align: center;
    }

    .sing-in-link {
        text-align: right;
    }

    @media screen and (max-width: 767px) {
        .language-selector,
        .brand-link,
        .sing-in-link {
            text-align: center;
        }
    }
</style>

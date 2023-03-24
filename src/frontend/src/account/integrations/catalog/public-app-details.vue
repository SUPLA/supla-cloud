<template>
    <div class="container app-details-page">
        <div class="row">
            <div class="col-sm-3">
                <img class="app-image"
                    :src="'https://www.supla.org/assets/img/AD/' + app.id">
            </div>
            <div class="col-sm-9">
                <i18n-text :text="app.longDescription || app.description"
                    html="true"></i18n-text>
            </div>
        </div>
        <div class="row"
            v-if="app.defaultScope">
            <div class="col-xs-12">
                <h3>{{ $t('Application requirements') }}</h3>
                <oauth-scope-preview :desired-scopes="app.defaultScope"></oauth-scope-preview>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 text-center">
                <a :href="app.websiteUrl"
                    v-if="app.websiteUrl"
                    class="btn btn-lg btn-white">
                    <i class="pe-7s-global"></i>
                    {{ $t('Visit app webpage') }}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import OauthScopePreview from "../../../login/oauth-scope-preview.vue";
    import I18nText from "./i18n-text.vue";

    export default {
        props: ['app'],
        components: {
            I18nText,
            OauthScopePreview,

        },
        data() {
            return {
                error: undefined
            };
        },
        methods: {},
        computed: {
            oauthLink() {
                // const server = 'http://supla.local';
                const server = 'https://cloud.supla.org';
                return `${server}/oauth/v2/auth?client_id=${this.app.clientId}&redirect_uri=${this.app.defaultRedirectUri}` +
                    `&scope=${this.app.defaultScope}&response_type=code`;
            }
        },
    };
</script>

<style lang="scss">
    .app-details-page {
        img {
            max-width: 100%;
        }
    }
</style>

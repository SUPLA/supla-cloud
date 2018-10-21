<template>
    <div class="container app-details-page">
        <div class="row">
            <div class="col-sm-3">
                <img class="app-image"
                    :src="'https://api.thecatapi.com/v1/images/search?format=src&size=full&' + app.id">
            </div>
            <div class="col-sm-9">
                {{ app.description }}
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <h3>{{ $t('Application requirements') }}</h3>
                <oauth-scope-preview :desired-scopes="app.defaultScope"></oauth-scope-preview>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 text-center">
                <a href=""
                    class="btn btn-lg btn-white">
                    <i class="pe-7s-global"></i>
                    {{ $t('Visit app webpage' )}}
                </a>
            </div>
            <div class="col-sm-6 text-center">
                <a :href="oauthLink"
                    class="btn btn-lg btn-green">
                    <i class="pe-7s-plus"></i>
                    {{ $t('Add to your account' )}}
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import PageContainer from "../../common/pages/page-container";
    import OauthScopePreview from "../../login/oauth-scope-preview";

    export default {
        props: ['app'],
        components: {
            OauthScopePreview,
            PageContainer,

        },
        data() {
            return {
                error: undefined
            };
        },
        methods: {},
        computed: {
            oauthLink() {
                const server = 'http://supla.local';
                // const server = 'https://cloud.supla.org';
                return `${server}/oauth/v2/auth?client_id=${this.app.id}&redirect_uri=${this.app.defaultRedirectUri}` +
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

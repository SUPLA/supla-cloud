<template>
    <div>
        <div class="container">
            <h5>{{ $t('If you are a developer who wants to provide additional features for SUPLA, create an OAuth application that users can authorize and use.') }}</h5>
            <div class="form-group">
                <div class="btn-group">
                    <!--<a href="https://github.com/SUPLA/supla-cloud/wiki/Integrations#oauth-apps"-->
                    <!--class="btn btn-green">-->
                    <!--{{ $t('Full documentation')}}: GitHub-->
                    <!--</a>-->
                    <a href="/api/docs.html"
                        target="_blank"
                        class="btn btn-white">
                        {{ $t('API documentation')}}
                        <i class="pe-7s-exapnd2"></i>
                    </a>
                </div>
            </div>
        </div>
        <carousel-page header-i18n="My OAuth apps"
            tile="my-oauth-app-tile"
            endpoint="oauth-clients"
            create-new-label-i18n="Register a new OAuth application"
            details-route="myOauthApp"
            :limit="$user.userData.limits.oauthClient"
            class="my-oauth-apps-page"></carousel-page>
    </div>
</template>

<script>
    import CarouselPage from "src/common/pages/carousel-page";
    import MyOauthAppTile from "./my-oauth-app-tile";
    import Vue from "vue";

    Vue.component('MyOauthAppTile', MyOauthAppTile);

    export default {
        components: {CarouselPage}
    };
</script>

<style>
    .my-oauth-apps-page .carousel-title {
        display: none;
    }
</style>

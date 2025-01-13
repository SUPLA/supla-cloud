<template>
    <div>
        <div class="container">
            <h5>{{ $t('If you are a developer who wants to provide additional features for SUPLA, create an OAuth application that users can authorize and use.') }}</h5>
            <div class="form-group">
                <div class="btn-group">
                    <a href="https://github.com/SUPLA/supla-cloud/wiki/Integrations#oauth-apps"
                        class="btn btn-white">
                        {{ $t('Full documentation') }} @ GitHub
                    </a>
                    <a :href="'/api-docs/docs.html' | withBaseUrl"
                        target="_blank"
                        class="btn btn-white">
                        {{ $t('API documentation') }}
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
            :limit="userData.limits.oauthClient"
            class="my-oauth-apps-page"></carousel-page>
    </div>
</template>

<script>
    import CarouselPage from "../../../common/pages/carousel-page.vue";
    import MyOauthAppTile from "./my-oauth-app-tile.vue";
    import Vue from "vue";
    import {mapState} from "pinia";
    import {useCurrentUserStore} from "@/stores/current-user-store";

    Vue.component('MyOauthAppTile', MyOauthAppTile);

    export default {
        components: {CarouselPage},
        computed: {
            ...mapState(useCurrentUserStore, ['userData']),
        },
    };
</script>

<style>
    .my-oauth-apps-page .carousel-title {
        display: none;
    }
</style>

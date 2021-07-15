<template>
    <list-page header-i18n="Direct links"
        tile="direct-link-tile"
        filters="direct-link-filters"
        endpoint="direct-links?include=subject"
        create-new-label-i18n="Create new direct link"
        :limit="$user.userData.limits.directLink"
        details-route="directLink"
        :subject="subject"></list-page>
</template>

<script>
    import DirectLinkTile from "./direct-link-tile";
    import DirectLinkFilters from "./direct-link-filters";
    import ListPage from "../common/pages/list-page";
    import Vue from "vue";

    Vue.component('DirectLinkTile', DirectLinkTile);
    Vue.component('DirectLinkFilters', DirectLinkFilters);

    export default {
        props: ['subject'],
        components: {ListPage},
    };
</script>

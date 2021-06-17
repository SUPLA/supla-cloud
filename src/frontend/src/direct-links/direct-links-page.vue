<template>
    <carousel-page header-i18n="Direct links"
        tile="direct-link-tile"
        endpoint="direct-links?include=subject"
        create-new-label-i18n="Create new direct link"
        :limit="$user.userData.limits.directLink"
        filters="direct-link-filters"
        list="direct-links-list"
        details-route="directLink"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import DirectLinkTile from "./direct-link-tile";
    import DirectLinksList from "./direct-links-list";
    import DirectLinkFilters from "./direct-link-filters";
    import Vue from "vue";

    Vue.component('DirectLinkTile', DirectLinkTile);
    Vue.component('DirectLinksList', DirectLinksList);
    Vue.component('DirectLinkFilters', DirectLinkFilters);

    export default {
        components: {CarouselPage}
    };
</script>

<template>
    <carousel-page header="Direct links"
        tile="direct-link-tile"
        endpoint="direct-links?include=subject"
        create-new-label="Create new direct link"
        :limit="$user.userData.limits.directLink"
        details-route="directLink"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import DirectLinkTile from "./direct-link-tile";
    import Vue from "vue";

    Vue.component('DirectLinkTile', DirectLinkTile);

    export default {
        components: {CarouselPage}
    };
</script>

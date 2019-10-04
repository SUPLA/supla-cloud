<template>
    <carousel-page header-i18n="Locations"
        tile="location-tile"
        filters="location-filters"
        endpoint="locations?include=childrenIds"
        create-new-label-i18n="Create New Location"
        :limit="$user.userData.limits.location"
        details-route="location"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import LocationTile from "./location-tile";
    import LocationFilters from "./location-filters";
    import Vue from "vue";

    Vue.component('LocationTile', LocationTile);
    Vue.component('LocationFilters', LocationFilters);

    export default {
        components: {CarouselPage}
    };
</script>

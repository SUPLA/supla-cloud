<template>
    <carousel-page header-i18n="Locations"
        tile="location-tile"
        filters="location-filters"
        endpoint="locations"
        create-new-label-i18n="Create New Location"
        :limit="userData.limits.location"
        details-route="location"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import LocationTile from "./location-tile";
    import LocationFilters from "./location-filters";
    import Vue from "vue";
    import {mapState} from "pinia";
    import {useCurrentUserStore} from "@/stores/current-user-store";

    Vue.component('LocationTile', LocationTile);
    Vue.component('LocationFilters', LocationFilters);

    export default {
        components: {CarouselPage},
        computed: {
            ...mapState(useCurrentUserStore, ['userData']),
        },
    };
</script>

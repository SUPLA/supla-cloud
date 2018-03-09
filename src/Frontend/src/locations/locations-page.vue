<template>
    <carousel-page header="Locations"
        :selected-id="id"
        tile="location-tile"
        details="location-details"
        filters="location-filters"
        endpoint="locations"
        create-new-label="Create New Location"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import LocationDetails from "./location-details";
    import LocationTile from "./location-tile";
    import LocationFilters from "./location-filters";
    import Vue from "vue";

    Vue.component('LocationTile', LocationTile);
    Vue.component('LocationDetails', LocationDetails);
    Vue.component('LocationFilters', LocationFilters);

    export default {
        props: ['id'],
        components: {CarouselPage}
    };
</script>

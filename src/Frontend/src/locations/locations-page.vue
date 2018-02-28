<template>
    <carousel-page header="Locations"
        tile="location-tile"
        details="location-details"
        endpoint="locations"
        create-new-label="Create New Location"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import LocationDetails from "./location-details";
    import LocationTile from "./location-tile";
    import Vue from "vue";

    Vue.component('LocationTile', LocationTile);
    Vue.component('LocationDetails', LocationDetails);

    export default {
        components: {CarouselPage}
    };
</script>

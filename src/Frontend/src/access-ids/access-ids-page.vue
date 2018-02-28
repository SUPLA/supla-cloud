<template>
    <carousel-page header="Access Identifiers"
        tile="access-id-tile"
        details="access-id-details"
        endpoint="accessids"
        create-new-label="Create New Access Identifier"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import AccessIdDetails from "./access-id-details";
    import AccessIdTile from "./access-id-tile";
    import Vue from "vue";

    Vue.component('AccessIdTile', AccessIdTile);
    Vue.component('AccessIdDetails', AccessIdDetails);

    export default {
        components: {CarouselPage}
    };
</script>

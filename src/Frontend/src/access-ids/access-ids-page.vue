<template>
    <carousel-page header="Access Identifiers"
        :selected-id="id"
        tile="access-id-tile"
        details="access-id-details"
        filters="access-id-filters"
        endpoint="accessids"
        create-new-label="Create New Access Identifier"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import AccessIdDetails from "./access-id-details";
    import AccessIdTile from "./access-id-tile";
    import AccessIdFilters from "./access-id-filters";
    import Vue from "vue";

    Vue.component('AccessIdTile', AccessIdTile);
    Vue.component('AccessIdDetails', AccessIdDetails);
    Vue.component('AccessIdFilters', AccessIdFilters);

    export default {
        props: ['id'],
        components: {CarouselPage}
    };
</script>

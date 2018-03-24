<template>
    <carousel-page header="Access Identifiers"
        tile="access-id-tile"
        filters="access-id-filters"
        endpoint="accessids"
        create-new-label="Create New Access Identifier"
        :limit="$user.limits.accessId"
        details-route="accessId"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import AccessIdTile from "./access-id-tile";
    import AccessIdFilters from "./access-id-filters";
    import Vue from "vue";

    Vue.component('AccessIdTile', AccessIdTile);
    Vue.component('AccessIdFilters', AccessIdFilters);

    export default {
        components: {CarouselPage}
    };
</script>

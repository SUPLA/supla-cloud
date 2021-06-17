<template>
    <carousel-page header-i18n="Access Identifiers"
        tile="access-id-tile"
        filters="access-id-filters"
        endpoint="accessids"
        create-new-label-i18n="Create New Access Identifier"
        :limit="$user.userData.limits.accessId"
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

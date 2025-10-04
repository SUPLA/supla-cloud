<template>
    <carousel-page header-i18n="Locations"
        :tile="LocationTile"
        :filters="LocationFilters"
        endpoint="locations"
        :store="locationsStore"
        create-new-label-i18n="Create New Location"
        :limit="userData.limits.location"
        details-route="location"></carousel-page>
</template>

<script setup>
  import CarouselPage from "../common/pages/carousel-page.vue";
  import LocationTile from "./location-tile.vue";
  import LocationFilters from "./location-filters.vue";
  import {storeToRefs} from "pinia";
  import {useCurrentUserStore} from "@/stores/current-user-store";
  import {useLocationsStore} from "@/stores/locations-store";

  const {userData} = storeToRefs(useCurrentUserStore());
    const locationsStore = useLocationsStore();
</script>

<template>
    <carousel-page header-i18n="Direct links"
        :tile="DirectLinkTile"
        endpoint="direct-links?include=subject"
        create-new-label-i18n="Create new direct link"
        :limit="userData.limits.directLink"
        :filters="DirectLinkFilters"
        :list="DirectLinksList"
        details-route="directLink"></carousel-page>
</template>

<script setup>
  import CarouselPage from "../common/pages/carousel-page.vue";
  import DirectLinkTile from "./direct-link-tile.vue";
  import DirectLinksList from "./direct-links-list.vue";
  import DirectLinkFilters from "./direct-link-filters.vue";
  import {storeToRefs} from "pinia";
  import {useCurrentUserStore} from "@/stores/current-user-store";

  const {userData} = storeToRefs(useCurrentUserStore());
</script>

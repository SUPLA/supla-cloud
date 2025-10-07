<template>
    <list-page header-i18n="Direct links"
        :tile="DirectLinkTile"
        :filters="DirectLinkFilters"
        endpoint="direct-links?include=subject"
        create-new-label-i18n="Create new direct link"
        :limit="userData.limits.directLink"
        details-route="directLink"
        :subject="subject"></list-page>
</template>

<script setup>
  import DirectLinkTile from "./direct-link-tile.vue";
  import DirectLinkFilters from "./direct-link-filters.vue";
  import ListPage from "../common/pages/list-page.vue";
  import {storeToRefs} from "pinia";
  import {useCurrentUserStore} from "@/stores/current-user-store";

  defineProps({subject: Object});

    const {userData} = storeToRefs(useCurrentUserStore());
</script>

<template>
    <carousel-page header-i18n="Channel groups"
        :tile="ChannelGroupTile"
        :filters="ChannelGroupFilters"
        endpoint="channel-groups"
        create-new-label-i18n="Add new channel group"
        :limit="userData.limits.channelGroup"
        details-route="channelGroup"></carousel-page>
</template>

<script setup>
  import CarouselPage from "../common/pages/carousel-page.vue";
  import ChannelGroupTile from "./channel-group-tile.vue";
  import ChannelGroupFilters from "./channel-group-filters.vue";
  import {storeToRefs} from "pinia";
  import {useCurrentUserStore} from "@/stores/current-user-store";

  const {userData} = storeToRefs(useCurrentUserStore());
</script>

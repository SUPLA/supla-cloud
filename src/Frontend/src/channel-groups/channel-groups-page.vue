<template>
    <carousel-page header="Channel groups"
        tile="channel-group-tile"
        filters="channel-group-filters"
        endpoint="channel-groups"
        create-new-label="Create new channel group"
        details-route="channelGroup"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import ChannelGroupTile from "./channel-group-tile";
    import ChannelGroupFilters from "./channel-group-filters";
    import Vue from "vue";

    Vue.component('ChannelGroupTile', ChannelGroupTile);
    Vue.component('ChannelGroupFilters', ChannelGroupFilters);

    export default {
        components: {CarouselPage}
    };
</script>

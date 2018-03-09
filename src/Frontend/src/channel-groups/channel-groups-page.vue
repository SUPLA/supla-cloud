<template>
    <carousel-page header="Channel groups"
        :selected-id="id"
        tile="channel-group-tile"
        details="channel-group-details"
        filters="channel-group-filters"
        endpoint="channel-groups"
        create-new-label="Create new channel group"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import ChannelGroupDetails from "./channel-group-details";
    import ChannelGroupTile from "./channel-group-tile";
    import ChannelGroupFilters from "./channel-group-filters";
    import Vue from "vue";

    Vue.component('ChannelGroupTile', ChannelGroupTile);
    Vue.component('ChannelGroupDetails', ChannelGroupDetails);
    Vue.component('ChannelGroupFilters', ChannelGroupFilters);

    export default {
        props: ['id'],
        components: {CarouselPage}
    };
</script>

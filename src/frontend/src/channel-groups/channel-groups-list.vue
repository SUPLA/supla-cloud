<template>
    <div>
        <list-page header-i18n="Channel groups"
            tile="channel-group-tile"
            filters="channel-group-filters"
            endpoint="channel-groups"
            create-new-label-i18n="Add new channel group"
            :limit="userData.limits.channelGroup"
            details-route="channelGroup"
            :subject="channel"></list-page>
    </div>
</template>

<script>
    import ListPage from "../common/pages/list-page";
    import ChannelGroupTile from "./channel-group-tile";
    import ChannelGroupFilters from "./channel-group-filters";
    import Vue from "vue";
    import {mapState} from "pinia";
    import {useCurrentUserStore} from "@/stores/current-user-store";

    Vue.component('ChannelGroupTile', ChannelGroupTile);
    Vue.component('ChannelGroupFilters', ChannelGroupFilters);

    export default {
        props: ['channel'],
        components: {ListPage},
        computed: {
            ...mapState(useCurrentUserStore, ['userData']),
        },
    };
</script>

<template>
    <div>
        <loading-cover :loading="!channels">
            <div class="container" v-show="channels && channels.length">
                <channel-filters
                    @filter-function="filterFunction = $event"
                    @compare-function="compareFunction = $event"></channel-filters>
            </div>
            <div v-if="channels && channels.length">
                <square-links-grid v-if="filteredChannels.length"
                    :count="filteredChannels.length">
                    <div v-for="channel in filteredChannels" :key="channel.id">
                        <channel-tile :model="channel"></channel-tile>
                    </div>
                </square-links-grid>
                <empty-list-placeholder v-else></empty-list-placeholder>
            </div>
            <empty-list-placeholder v-else-if="channels"></empty-list-placeholder>
        </loading-cover>
    </div>
</template>

<script>
    import ChannelFilters from "./channel-filters";
    import ChannelTile from "./channel-tile";
    import {mapState} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";

    export default {
        components: {ChannelTile, ChannelFilters},
        data() {
            return {
                filterFunction: () => true,
                compareFunction: () => -1,
            };
        },
        computed: {
            ...mapState(useChannelsStore, {channels: 'list'}),
            filteredChannels() {
                const filteredChannels = this.channels
                    .filter(this.filterFunction)
                    .filter((channel) => !channel.config.hideInChannelsList);
                filteredChannels.sort(this.compareFunction);
                return filteredChannels;
            },
        },
    };
</script>

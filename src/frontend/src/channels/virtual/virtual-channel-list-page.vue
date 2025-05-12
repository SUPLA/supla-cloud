<template>
    <div>
        <loading-cover :loading="!channels">
            <div class="container" v-show="channels && channels.length">
                <virtual-channel-filters
                    @filter-function="filterFunction = $event"
                    @compare-function="compareFunction = $event"/>
                <h4 v-if="allVirtualChannels.length === 0">{{ $t('Data sources are virtual channels that can provide data for your account.') }}</h4>
            </div>
            <div>
                <square-links-grid :count="filteredChannels.length + 1">
                    <div v-for="channel in filteredChannels" :key="channel.id">
                        <NewVirtualChannelForm v-if="channel.id === 'new'"/>
                        <channel-tile v-else :model="channel"></channel-tile>
                    </div>
                </square-links-grid>
            </div>
        </loading-cover>
    </div>
</template>

<script>
    import ChannelTile from "../channel-tile";
    import {mapState} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";
    import ChannelType from "@/common/enums/channel-type";
    import VirtualChannelFilters from "@/channels/virtual/virtual-channel-filters.vue";
    import NewVirtualChannelForm from "@/channels/virtual/new-virtual-channel-form.vue";

    export default {
        components: {NewVirtualChannelForm, VirtualChannelFilters, ChannelTile},
        data() {
            return {
                filterFunction: () => true,
                compareFunction: () => -1,
            };
        },
        computed: {
            ...mapState(useChannelsStore, {channels: 'list'}),
            allVirtualChannels() {
                return this.channels
                    .filter((channel) => !channel.config.hideInChannelsList)
                    .filter((channel) => !channel.typeId === ChannelType.VIRTUAL);
            },
            filteredChannels() {
                const filteredChannels = this.allVirtualChannels.filter(this.filterFunction);
                filteredChannels.sort(this.compareFunction);
                filteredChannels.push({id: 'new'})
                return filteredChannels;
            },
        },
    };
</script>

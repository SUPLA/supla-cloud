<template>
    <div>
        <loading-cover :loading="!channels">
            <div class="container mb-3">
                <div class="d-flex">
                    <h4 class="flex-grow-1">{{ $t('Data sources are virtual channels that can provide data for your account.') }}</h4>
                    <NewVirtualChannelButton/>
                </div>
            </div>
            <div class="container" v-show="allVirtualChannels && allVirtualChannels.length">
                <virtual-channel-filters
                    @filter-function="filterFunction = $event"
                    @compare-function="compareFunction = $event"/>
            </div>
            <div>
                <square-links-grid :count="filteredChannels.length + 1" v-if="allVirtualChannels?.length > 0">
                    <div v-for="channel in filteredChannels" :key="channel.id">
                        <channel-tile :model="channel"></channel-tile>
                    </div>
                </square-links-grid>
                <empty-list-placeholder v-else-if="allVirtualChannels"/>
            </div>
        </loading-cover>
    </div>
</template>

<script>
  import ChannelTile from "@/channels/channel-tile.vue";
  import {mapState} from "pinia";
  import {useChannelsStore} from "@/stores/channels-store";
  import VirtualChannelFilters from "./virtual-channel-filters.vue";
  import NewVirtualChannelButton
    from "@/account/integrations/data-sources/new-virtual-channel-button.vue";

  export default {
        components: {NewVirtualChannelButton, VirtualChannelFilters, ChannelTile},
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
                    .filter((channel) => channel.isVirtual);
            },
            filteredChannels() {
                const filteredChannels = this.allVirtualChannels.filter(this.filterFunction);
                filteredChannels.sort(this.compareFunction);
                return filteredChannels;
            },
        },
    };
</script>

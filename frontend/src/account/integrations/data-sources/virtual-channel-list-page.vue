<template>
  <div>
    <loading-cover :loading="!channels">
      <div class="container mb-3">
        <div class="d-flex">
          <h4 class="flex-grow-1">{{ $t('Data sources are virtual channels that can provide data for your account.') }}</h4>
          <NewVirtualChannelButton />
        </div>
      </div>
      <div v-show="allVirtualDataSources && allVirtualDataSources.length" class="container">
        <virtual-channel-filters @filter-function="filterFunction = $event" @compare-function="compareFunction = $event" />
      </div>
      <div>
        <square-links-grid v-if="allVirtualDataSources?.length > 0" :count="filteredChannels.length + 1">
          <div v-for="channel in filteredChannels" :key="channel.id">
            <channel-tile :model="channel"></channel-tile>
          </div>
        </square-links-grid>
        <empty-list-placeholder v-else-if="allVirtualDataSources" />
      </div>
    </loading-cover>
  </div>
</template>

<script>
  import ChannelTile from '@/channels/channel-tile.vue';
  import {mapState} from 'pinia';
  import {useChannelsStore} from '@/stores/channels-store';
  import VirtualChannelFilters from './virtual-channel-filters.vue';
  import NewVirtualChannelButton from '@/account/integrations/data-sources/new-virtual-channel-button.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import SquareLinksGrid from '@/common/tiles/square-links-grid.vue';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';

  export default {
    components: {
      EmptyListPlaceholder,
      SquareLinksGrid,
      LoadingCover,
      NewVirtualChannelButton,
      VirtualChannelFilters,
      ChannelTile,
    },
    data() {
      return {
        filterFunction: () => true,
        compareFunction: () => -1,
      };
    },
    computed: {
      ...mapState(useChannelsStore, {channels: 'list'}),
      allVirtualDataSources() {
        return this.channels.filter((channel) => !channel.config.hideInChannelsList).filter((channel) => channel.virtualType === 'DATA_SOURCE');
      },
      filteredChannels() {
        const filteredChannels = this.allVirtualDataSources.filter(this.filterFunction);
        filteredChannels.sort(this.compareFunction);
        return filteredChannels;
      },
    },
  };
</script>

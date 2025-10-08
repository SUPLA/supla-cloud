<template>
  <div>
    <list-page
      header-i18n="Channel groups"
      :tile="ChannelGroupTile"
      :filters="ChannelGroupFilters"
      endpoint="channel-groups"
      create-new-label-i18n="Add new channel group"
      :limit="userData.limits.channelGroup"
      details-route="channelGroup"
      :subject="channel"
    ></list-page>
  </div>
</template>

<script setup>
  import ListPage from '../common/pages/list-page.vue';
  import ChannelGroupTile from './channel-group-tile.vue';
  import ChannelGroupFilters from './channel-group-filters.vue';
  import {storeToRefs} from 'pinia';
  import {useCurrentUserStore} from '@/stores/current-user-store';

  defineProps({channel: Object});

  const {userData} = storeToRefs(useCurrentUserStore());
</script>

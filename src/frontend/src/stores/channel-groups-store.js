import {defineStore} from 'pinia';
import {channelGroupsApi} from '@/api/channel-groups-api.js';
import {useFetchList} from '@/stores/index.js';

export const useChannelGroupsStore = defineStore('channelGroups', () => {
  const {all, ids, list, $reset, fetchAll} = useFetchList(channelGroupsApi.getList);
  return {all, ids, list, $reset, fetchAll};
});

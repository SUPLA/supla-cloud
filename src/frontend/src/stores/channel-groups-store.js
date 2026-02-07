import {defineStore} from 'pinia';
import {channelGroupsApi} from '@/api/channel-groups-api.js';
import {useFetchList} from '@/stores/index.js';

export const useChannelGroupsStore = defineStore('channelGroups', () => {
  const {all, ids, list, ready, updateOne, $reset, fetchAll} = useFetchList(channelGroupsApi.getList);

  const fetchOne = (id) => {
    return channelGroupsApi.getOne(id).then((scene) => {
      updateOne(scene);
      return scene;
    });
  };

  return {all, ids, list, ready, fetchOne, $reset, fetchAll};
});

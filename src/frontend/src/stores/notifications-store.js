import {defineStore} from 'pinia';
import {scenesApi} from '@/api/scenes-api.js';
import {useFetchList} from '@/stores/index.js';

export const useNotificationsStore = defineStore('notifications', () => {
  const {all, ids, list, ready, $reset, fetchAll} = useFetchList(scenesApi);
  return {all, ids, list, ready, $reset, fetchAll};
});

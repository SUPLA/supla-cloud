import {defineStore} from 'pinia';
import {scenesApi} from '@/api/scenes-api.js';
import {useFetchList} from '@/stores/index.js';

export const useScenesStore = defineStore('scenes', () => {
  const {all, ids, list, $reset, fetchAll} = useFetchList(scenesApi.getList);
  return {all, ids, list, $reset, fetchAll};
});

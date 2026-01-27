import {defineStore} from 'pinia';
import {reactionsApi} from '@/api/reactions-api.js';
import {useFetchList} from '@/stores/index.js';

export const useReactionsStore = defineStore('reactions', () => {
  const {all, ids, list, $reset, fetchAll} = useFetchList(reactionsApi.getList);
  return {all, ids, list, $reset, fetchAll};
});

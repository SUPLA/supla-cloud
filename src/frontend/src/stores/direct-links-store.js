import {defineStore} from 'pinia';
import {useFetchList} from '@/stores/index.js';
import {directLinksApi} from '@/api/direct-links-api.js';

export const useDirectLinksStore = defineStore('directLinks', () => {
  const {all, ids, list, ready, $reset, fetchAll} = useFetchList(directLinksApi.getList);
  return {all, ids, list, ready, $reset, fetchAll};
});

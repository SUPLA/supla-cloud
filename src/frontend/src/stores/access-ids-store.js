import {defineStore} from 'pinia';
import {accessIdsApi} from '@/api/access-ids-api';
import {useFetchList} from '@/stores/index.js';

export const useAccessIdsStore = defineStore('accessIds', () => {
  const {all, ids, list, $reset, fetchAll} = useFetchList(accessIdsApi.getList);
  return {all, ids, list, $reset, fetchAll};
});

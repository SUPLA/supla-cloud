import {defineStore} from 'pinia';
import {accessIdsApi} from '@/api/access-ids-api';
import {useEnsureStoreLoaded, useFetchList} from '@/stores/index.js';

export const useAccessIdsStore = defineStore('accessIds', () => {
  const {all, ids, list, ready, $reset, fetchAll} = useFetchList(accessIdsApi.getList);
  return {all, ids, list, ready, $reset, fetchAll};
});

export function useAccessIds(options) {
  const {store} = useEnsureStoreLoaded(useAccessIdsStore(), options);
  return store;
}

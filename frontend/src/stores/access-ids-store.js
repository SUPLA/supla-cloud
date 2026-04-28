import {defineStore} from 'pinia';
import {accessIdsApi} from '@/api/access-ids-api';
import {useEnsureStoreLoaded, useFetchList} from '@/stores/index.js';

export const useAccessIdsStore = defineStore('accessIds', () => {
  const {all, ids, list, ready, updating, createInternal, $reset, fetchAll} = useFetchList(accessIdsApi);

  const create = () => createInternal();

  function forLocation(location) {
    return list.value.filter((accessId) => accessId.locationsIds.includes(location?.id));
  }

  return {all, ids, list, ready, updating, create, forLocation, $reset, fetchAll};
});

export function useAccessIds(options) {
  const {store} = useEnsureStoreLoaded(useAccessIdsStore(), options);
  return store;
}

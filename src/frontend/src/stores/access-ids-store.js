import {defineStore} from 'pinia';
import {accessIdsApi} from '@/api/access-ids-api';
import {useEnsureStoreLoaded, useFetchList} from '@/stores/index.js';
import {ref} from 'vue';

export const useAccessIdsStore = defineStore('accessIds', () => {
  const {all, ids, list, ready, $reset, fetchAll} = useFetchList(accessIdsApi.getList);

  const updating = ref(false);

  async function create() {
    updating.value = true;
    try {
      const newItem = await accessIdsApi.create();
      all.value[newItem.id] = newItem;
      return newItem;
    } finally {
      updating.value = false;
    }
  }

  function forLocation(location) {
    return list.value.filter((accessId) => accessId.locationsIds.includes(location?.id));
  }

  return {all, ids, list, ready, updating, create, forLocation, $reset, fetchAll};
});

export function useAccessIds(options) {
  const {store} = useEnsureStoreLoaded(useAccessIdsStore(), options);
  return store;
}

import {defineStore} from 'pinia';
import {locationsApi} from '@/api/locations-api';
import {useEnsureStoreLoaded, useFetchList} from '@/stores/index.js';

export const useLocationsStore = defineStore('locations', () => {
  const {all, ids, list, ready, updating, updateOne, updateInternal, removeInternal, createInternal, $reset, fetchAll} = useFetchList(locationsApi);
  const create = () => createInternal();
  const update = (id, data) => updateInternal(id, data);
  const remove = (id) => removeInternal(id);
  return {all, ids, list, ready, updating, create, update, remove, updateOne, $reset, fetchAll};
});

export function useLocations(options) {
  const {store} = useEnsureStoreLoaded(useLocationsStore(), options);
  return store;
}

import {defineStore} from 'pinia';
import {locationsApi} from '@/api/locations-api';
import {useFetchList} from '@/stores/index.js';

export const useLocationsStore = defineStore('locations', () => {
  const {all, ids, list, updateOne, $reset, fetchAll} = useFetchList(locationsApi.getList);
  return {all, ids, list, updateOne, $reset, fetchAll};
});

import {defineStore} from 'pinia';
import {clientAppsApi} from '@/api/client-apps-api';
import {useFetchList} from '@/stores/index.js';

export const useClientAppsStore = defineStore('clientApps', () => {
  const {all, ids, list, $reset, fetchAll} = useFetchList(clientAppsApi.getList);
  return {all, ids, list, $reset, fetchAll};
});

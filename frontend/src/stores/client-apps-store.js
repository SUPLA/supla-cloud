import {defineStore} from 'pinia';
import {clientAppsApi} from '@/api/client-apps-api';
import {useFetchList} from '@/stores/index.js';

export const useClientAppsStore = defineStore('clientApps', () => {
  const {all, ids, list, $reset, fetchAll} = useFetchList(clientAppsApi);
  return {all, ids, list, $reset, fetchAll};
});

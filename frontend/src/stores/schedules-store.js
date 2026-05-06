import {defineStore} from 'pinia';
import {useFetchList} from '@/stores/index.js';
import {schedulesApi} from '@/api/schedules-api.js';

export const useSchedulesStore = defineStore('schedules', () => {
  const {all, ids, list, ready, $reset, fetchAll} = useFetchList(schedulesApi);
  return {all, ids, list, ready, $reset, fetchAll};
});

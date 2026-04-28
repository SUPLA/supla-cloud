import {defineStore} from 'pinia';
import {useEnsureStoreLoaded, useFetchList} from '@/stores/index.js';
import {notificationsApi} from '@/api/notifications-api.js';

export const useNotificationsStore = defineStore('notifications', () => {
  const {all, ids, list, ready, $reset, fetchAll} = useFetchList(notificationsApi);
  return {all, ids, list, ready, $reset, fetchAll};
});

export function useNotifications(options) {
  const {store} = useEnsureStoreLoaded(useNotificationsStore(), options);
  return store;
}

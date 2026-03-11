import {defineStore} from 'pinia';
import {reactionsApi} from '@/api/reactions-api.js';
import {useEnsureStoreLoaded, useFetchList} from '@/stores/index.js';

export const useReactionsStore = defineStore('reactions', () => {
  const {all, ids, list, $reset, fetchAll} = useFetchList(reactionsApi);
  return {all, ids, list, $reset, fetchAll};
});

export function useReactions(options) {
  const {store} = useEnsureStoreLoaded(useReactionsStore(), options);
  return store;
}

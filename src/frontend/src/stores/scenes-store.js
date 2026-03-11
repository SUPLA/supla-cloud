import {defineStore} from 'pinia';
import {scenesApi} from '@/api/scenes-api.js';
import {useEnsureStoreLoaded, useFetchList} from '@/stores/index.js';

export const useScenesStore = defineStore('scenes', () => {
  const {all, ids, list, ready, updateOne, $reset, fetchAll} = useFetchList(scenesApi);

  const fetchOne = (id) => {
    return scenesApi.getOne(id).then((scene) => {
      updateOne(scene);
      return scene;
    });
  };

  return {all, ids, list, ready, fetchOne, $reset, fetchAll};
});

export function useScenes(options) {
  const {store} = useEnsureStoreLoaded(useScenesStore(), options);
  return store;
}

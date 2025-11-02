import {defineStore} from 'pinia';
import {computed, ref} from 'vue';
import {scenesApi} from '@/api/scenes-api.js';

export const useScenesStore = defineStore('scenes', () => {
  const all = ref({});
  const ids = ref([]);

  const fetchAll = (force = false) => {
    if (fetchAll.promise && !force) {
      return fetchAll.promise;
    } else {
      return (fetchAll.promise = scenesApi.getList().then((scenes) => {
        const state = scenes.reduce(
          (acc, curr) => {
            return {
              ids: acc.ids.concat(curr.id),
              all: {...acc.all, [curr.id]: curr},
            };
          },
          {ids: [], all: {}}
        );
        all.value = state.all;
        ids.value = state.ids;
      }));
    }
  };

  const list = computed(() => ids.value.map((id) => all.value[id]));

  const $reset = () => {
    all.value = {};
    ids.value = [];
    fetchAll.promise = undefined;
  };

  return {all, ids, list, $reset, fetchAll};
});

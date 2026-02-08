import {createPinia} from 'pinia';
import {computed, onMounted, ref} from 'vue';

export const pinia = createPinia();

export const useFetchList = (fetchListFn, idFactory = (item) => item.id) => {
  const all = ref({});
  const ready = ref(false);

  const fetchAll = (force = false) => {
    if (ready.value && !force) {
      return fetchAll.promise || Promise.resolve();
    }
    if (fetchAll.promise && !force) {
      return fetchAll.promise;
    }
    return (fetchAll.promise = fetchListFn().then((items) => {
      const state = {};
      items.forEach((item) => (state[idFactory(item)] = item));
      all.value = state;
      ready.value = true;
    }));
  };

  const list = computed(() => Object.values(all.value));
  const ids = computed(() => Object.keys(all.value));

  const updateOne = (newData) => {
    if (all.value[newData.id]) {
      all.value = {...all.value, [newData.id]: {...all.value[newData.id], ...newData}};
    }
  };

  const $reset = () => {
    ready.value = false;
    all.value = {};
    fetchAll.promise = undefined;
  };

  return {all, ids, list, ready, updateOne, $reset, fetchAll};
};

export function useEnsureStoreLoaded(store, options = {}) {
  const {immediate = true, force = false} = options;

  const ensure = () => store.fetchAll(force);

  if (immediate) {
    onMounted(() => {
      if (store.ready) return;
      ensure();
    });
  }

  return {store, ensure};
}

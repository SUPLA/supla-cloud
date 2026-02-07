import {createPinia} from 'pinia';
import {computed, ref} from 'vue';

export const pinia = createPinia();

export const useFetchList = (fetchListFn, idFactory = (item) => item.id) => {
  const all = ref({});
  const ids = ref([]);

  const ready = ref(false);

  const fetchAll = (force = false) => {
    if (ready.value && !force) {
      return fetchAll.promise || Promise.resolve();
    }
    if (fetchAll.promise && !force) {
      return fetchAll.promise;
    }
    return (fetchAll.promise = fetchListFn().then((items) => {
      const state = items.reduce(
        (acc, curr) => {
          const id = idFactory(curr);
          return {
            ids: acc.ids.concat(id),
            all: {...acc.all, [id]: curr},
          };
        },
        {ids: [], all: {}}
      );
      all.value = state.all;
      ids.value = state.ids;
      ready.value = true;
    }));
  };

  const list = computed(() => ids.value.map((id) => all.value[id]));

  const updateOne = (newData) => {
    if (all.value[newData.id]) {
      all.value = {...all.value, [newData.id]: {...all.value[newData.id], ...newData}};
    }
  };

  const $reset = () => {
    ready.value = false;
    all.value = {};
    ids.value = [];
    fetchAll.promise = undefined;
  };

  return {all, ids, list, ready, updateOne, $reset, fetchAll};
};

import {createPinia} from 'pinia';
import {computed, ref} from 'vue';

export const pinia = createPinia();

export const useFetchList = (fetchListFn) => {
  const all = ref({});
  const ids = ref([]);

  const fetchAll = (force = false) => {
    if (fetchAll.promise && !force) {
      return fetchAll.promise;
    } else {
      return (fetchAll.promise = fetchListFn().then((items) => {
        const state = items.reduce(
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

  const updateOne = (newData) => {
    if (all.value[newData.id]) {
      all.value = {...all.value, [newData.id]: {...all.value[newData.id], ...newData}};
    }
  };

  const $reset = () => {
    all.value = {};
    ids.value = [];
    fetchAll.promise = undefined;
  };

  return {all, ids, list, updateOne, $reset, fetchAll};
};

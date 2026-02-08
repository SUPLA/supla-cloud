import {createPinia} from 'pinia';
import {computed, onMounted, ref} from 'vue';

export const pinia = createPinia();

export const useFetchList = (theApi, options = {}) => {
  const all = ref({});
  const ready = ref(false);
  const updating = ref(false);

  const opts = {
    ...{
      idFactory: (item) => item.id,
    },
    ...options,
  };

  let fetchAllInFlight = null;

  const fetchAll = (force = false) => {
    if (!force) {
      if (ready.value) return fetchAllInFlight || Promise.resolve();
      if (fetchAllInFlight) return fetchAllInFlight;
    }
    fetchAllInFlight = theApi
      .getList()
      .then((items) => {
        const state = {};
        for (const item of items) state[opts.idFactory(item)] = item;
        all.value = state;
        ready.value = true;
      })
      .finally(() => {
        fetchAllInFlight = null;
      });
    return fetchAllInFlight;
  };

  const list = computed(() => Object.values(all.value));
  const ids = computed(() => Object.keys(all.value));

  const updateOne = (newData) => {
    if (all.value[newData.id]) {
      all.value[newData.id] = {...all.value[newData.id], ...newData};
    }
  };

  async function createInternal(...args) {
    updating.value = true;
    try {
      const lastArg = args[args.length - 1];
      const isCallback = typeof lastArg === 'function';
      const apiArgs = isCallback ? args.slice(0, -1) : args;
      const newItem = await theApi.create(...apiArgs);
      if (isCallback) {
        await lastArg(newItem);
      }
      all.value[newItem.id] = newItem;
      return newItem;
    } finally {
      updating.value = false;
    }
  }

  async function updateInternal(id, data) {
    const prev = all.value[id];
    all.value[id] = {...prev, ...data};
    updating.value = true;
    try {
      const item = await theApi.update(id, data);
      all.value[id] = item;
      return item;
    } catch (e) {
      all.value[id] = prev;
      throw e;
    } finally {
      updating.value = false;
    }
  }

  async function removeInternal(id) {
    updating.value = true;
    try {
      await theApi.delete_(id);
      // eslint-disable-next-line no-unused-vars
      const {[id]: _, ...rest} = all.value;
      all.value = rest;
    } finally {
      updating.value = false;
    }
  }

  const $reset = () => {
    ready.value = false;
    updating.value = false;
    all.value = {};
    fetchAll.promise = undefined;
  };

  return {all, ids, list, ready, updating, updateOne, createInternal, updateInternal, removeInternal, $reset, fetchAll};
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

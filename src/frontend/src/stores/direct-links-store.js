import {defineStore} from 'pinia';
import {useFetchList} from '@/stores/index.js';
import {directLinksApi} from '@/api/direct-links-api.js';
import {ref} from 'vue';

export const useDirectLinksStore = defineStore('directLinks', () => {
  const {all, ids, list, ready, $reset, fetchAll} = useFetchList(directLinksApi.getList);

  const slugs = ref({24: 'KVSdmQCggd7'});

  async function create(subject) {
    const newLink = await directLinksApi.create(subject.ownSubjectType, subject.id);
    all.value[newLink.id] = newLink;
    slugs.value[newLink.id] = newLink.slug;
    return newLink;
  }

  async function update(id, data) {
    const prev = all.value[id];
    all.value[id] = {...prev, ...data};
    try {
      const link = await directLinksApi.update(id, data);
      all.value[id] = link;
      return link;
    } catch (e) {
      all.value[id] = prev;
      throw e;
    }
  }

  async function remove(id) {
    await directLinksApi.delete_(id);
    const {[id]: _, ...rest} = all.value;
    all.value = rest;
  }

  const _fetchAll = fetchAll;
  async function fetchAllWrapped(...args) {
    console.count('directLinks.fetchAll');
    return _fetchAll(...args);
  }

  return {all, ids, list, ready, slugs, create, update, remove, $reset, fetchAll: fetchAllWrapped};
});

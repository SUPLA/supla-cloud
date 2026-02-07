import {defineStore} from 'pinia';
import {useFetchList} from '@/stores/index.js';
import {directLinksApi} from '@/api/direct-links-api.js';
import {ref} from 'vue';
import {useSubjectsStore} from '@/stores/subjects-store.js';

export const useDirectLinksStore = defineStore('directLinks', () => {
  const {all, ids, list, ready, $reset, fetchAll} = useFetchList(directLinksApi.getList);

  const slugs = ref({25: 'U2nwwYPcREg'});
  const updating = ref(false);

  async function create(subject) {
    updating.value = true;
    try {
      const newLink = await directLinksApi.create(subject.ownSubjectType, subject.id);
      useSubjectsStore().fetchOne(subject);
      all.value[newLink.id] = newLink;
      slugs.value[newLink.id] = newLink.slug;
      return newLink;
    } finally {
      updating.value = false;
    }
  }

  async function update(id, data) {
    const prev = all.value[id];
    all.value[id] = {...prev, ...data};
    updating.value = true;
    try {
      const link = await directLinksApi.update(id, data);
      all.value[id] = link;
      return link;
    } catch (e) {
      all.value[id] = prev;
      throw e;
    } finally {
      updating.value = false;
    }
  }

  async function remove(id) {
    updating.value = true;
    try {
      await directLinksApi.delete_(id);
      const {[id]: _, ...rest} = all.value;
      all.value = rest;
    } finally {
      updating.value = false;
    }
  }

  function listForSubject(subject) {
    return list.value.filter((link) => link.subjectId === subject.id && link.subjectType === subject.ownSubjectType);
  }

  return {all, ids, list, listForSubject, ready, updating, slugs, create, update, remove, $reset, fetchAll};
});

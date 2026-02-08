import {defineStore} from 'pinia';
import {useEnsureStoreLoaded, useFetchList} from '@/stores/index.js';
import {directLinksApi} from '@/api/direct-links-api.js';
import {ref} from 'vue';
import {useSubjectsStore} from '@/stores/subjects-store.js';

export const useDirectLinksStore = defineStore('directLinks', () => {
  const {all, ids, list, ready, updating, createInternal, updateInternal, removeInternal, $reset, fetchAll} = useFetchList(directLinksApi);

  const slugs = ref({});

  async function create(subject) {
    return await createInternal(subject.ownSubjectType, subject.id, async (newLink) => {
      await useSubjectsStore().fetchOne(subject);
      slugs.value[newLink.id] = newLink.slug;
    });
  }

  const update = (id, data) => updateInternal(id, data);
  const remove = (id) => removeInternal(id);

  function listForSubject(subject) {
    return list.value.filter((link) => link.subjectId === subject.id && link.subjectType === subject.ownSubjectType);
  }

  return {all, ids, list, listForSubject, ready, updating, slugs, create, update, remove, $reset, fetchAll};
});

export function useDirectLinks(options) {
  const {store} = useEnsureStoreLoaded(useDirectLinksStore(), options);
  return store;
}

<template>
  <div class="container text-right mb-3">
    <button class="btn btn-green btn-lg btn-wrapped" @click="createNewDirectLink()">
      <i class="pe-7s-plus"></i>
      {{ $t('Create new direct link') }}
    </button>
  </div>
  <LoadingCover :loading="!ready">
    <DirectLinksListNew :items="list" />
  </LoadingCover>
</template>

<script setup>
  import {useDirectLinksStore} from '@/stores/direct-links-store.js';
  import DirectLinksListNew from '@/direct-links/direct-links-list-new.vue';
  import {storeToRefs} from 'pinia';
  import {computed, onMounted} from 'vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {useRouter} from 'vue-router';

  const props = defineProps({subject: Object});

  const router = useRouter();
  const directLinksStore = useDirectLinksStore();
  const {ready} = storeToRefs(directLinksStore);
  onMounted(() => directLinksStore.fetchAll());

  const list = computed(() => directLinksStore.listForSubject(props.subject));

  async function createNewDirectLink() {
    const newLink = await directLinksStore.create(props.subject);
    await router.push({name: 'directLink', params: {id: newLink.id}});
  }
</script>

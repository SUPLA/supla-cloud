<template>
  <div class="container text-right mb-3">
    <FormButton button-class="btn-green btn-lg btn-wrapped" @click="createNewDirectLink()" :loading="updating">
      <i class="pe-7s-plus"></i>
      {{ $t('Create new direct link') }}
    </FormButton>
  </div>
  <LoadingCover :loading="!ready">
    <DirectLinksList :items="list" v-if="ready" />
  </LoadingCover>
</template>

<script setup>
  import {useDirectLinks} from '@/stores/direct-links-store.js';
  import {storeToRefs} from 'pinia';
  import {computed} from 'vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {useRouter} from 'vue-router';
  import FormButton from '@/common/gui/FormButton.vue';
  import DirectLinksList from '@/direct-links/direct-links-list.vue';

  const props = defineProps({subject: Object});

  const router = useRouter();
  const directLinksStore = useDirectLinks();
  const {ready, updating} = storeToRefs(directLinksStore);

  const list = computed(() => directLinksStore.listForSubject(props.subject));

  async function createNewDirectLink() {
    const newLink = await directLinksStore.create(props.subject);
    void router.push({name: 'directLink', params: {id: newLink.id}});
  }
</script>

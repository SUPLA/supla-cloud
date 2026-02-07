<template>
  <div class="container">
    <div class="d-flex mb-5 my-3">
      <div class="flex-grow-1">
        <h1 class="m-0">{{ $t('Direct links') }}</h1>
      </div>
      <div>
        <RouterLink class="btn btn-green btn-lg btn-wrapped" :to="{name: 'directLink.new'}">
          <i class="pe-7s-plus"></i>
          {{ $t('Create new direct link') }}
        </RouterLink>
      </div>
    </div>
  </div>
  <DirectLinksListNew :items="list" />
</template>

<script setup>
  import {useDirectLinksStore} from '@/stores/direct-links-store.js';
  import DirectLinksListNew from '@/direct-links/direct-links-list-new.vue';
  import {storeToRefs} from 'pinia';
  import {onMounted} from 'vue';

  const directLinksStore = useDirectLinksStore();
  const {list} = storeToRefs(directLinksStore);
  onMounted(() => directLinksStore.fetchAll());
</script>

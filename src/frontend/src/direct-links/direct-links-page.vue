<template>
  <div class="container">
    <div class="d-flex mb-5 my-3">
      <div class="flex-grow-1">
        <h1 class="m-0" v-title>{{ $t('Direct links') }}</h1>
      </div>
      <div>
        <RouterLink class="btn btn-green btn-lg btn-wrapped" :to="{name: 'directLink.new'}">
          <i class="pe-7s-plus"></i>
          {{ $t('Create new direct link') }}
        </RouterLink>
      </div>
    </div>
  </div>
  <LoadingCover :loading="!ready">
    <DirectLinksList :items="list" v-if="ready" />
  </LoadingCover>
</template>

<script setup>
  import {useDirectLinks} from '@/stores/direct-links-store.js';
  import {storeToRefs} from 'pinia';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import DirectLinksList from '@/direct-links/direct-links-list.vue';

  const {list, ready} = storeToRefs(useDirectLinks());
</script>

<template>
  <div class="container">
    <div class="d-flex mb-5 my-3 flex-wrap flex-gap-1">
      <div class="flex-grow-1">
        <h1 class="m-0" v-title>{{ $t('Scenes') }}</h1>
      </div>
      <div>
        <FormButton button-class="btn-green btn-lg btn-wrapped" @click="createNewScene()" :loading="updating">
          <i class="pe-7s-plus"></i>
          {{ $t('Create new scene') }}
        </FormButton>
      </div>
    </div>
  </div>
  <LoadingCover :loading="!ready">
    <ScenesList :items="list" v-if="ready" />
  </LoadingCover>
</template>

<script setup>
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {storeToRefs} from 'pinia';
  import FormButton from '@/common/gui/FormButton.vue';
  import {useRouter} from 'vue-router';
  import ScenesList from '@/scenes/scenes-list.vue';
  import {useScenes} from '@/stores/scenes-store.js';

  const router = useRouter();

  const store = useScenes();
  const {list, ready, updating} = storeToRefs(store);

  async function createNewScene() {
    await router.push({name: 'scene', params: {id: 'new'}});
  }
</script>

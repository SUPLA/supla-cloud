<template>
  <div class="container">
    <div class="d-flex mb-5 my-3 flex-wrap flex-gap-1">
      <div class="flex-grow-1">
        <h1 class="m-0" v-title>{{ $t('Locations') }}</h1>
      </div>
      <div>
        <FormButton button-class="btn-green btn-lg btn-wrapped" @click="createNewLocation()" :loading="updating">
          <i class="pe-7s-plus"></i>
          {{ $t('Create New Location') }}
        </FormButton>
      </div>
    </div>
  </div>
  <LoadingCover :loading="!ready">
    <LocationsList :items="list" v-if="ready" />
  </LoadingCover>
</template>

<script setup>
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {storeToRefs} from 'pinia';
  import FormButton from '@/common/gui/FormButton.vue';
  import {useRouter} from 'vue-router';
  import {useLocations} from '@/stores/locations-store.js';
  import LocationsList from '@/locations/locations-list.vue';

  const router = useRouter();

  const store = useLocations();
  const {list, ready, updating} = storeToRefs(store);

  async function createNewLocation() {
    const newItem = await store.create();
    await router.push({name: 'location', params: {id: newItem.id}});
  }
</script>

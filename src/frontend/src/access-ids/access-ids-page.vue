<template>
  <div class="container">
    <div class="d-flex mb-5 my-3">
      <div class="flex-grow-1">
        <h1 class="m-0" v-title>{{ $t('Access Identifiers') }}</h1>
      </div>
      <div>
        <FormButton button-class="btn-green btn-lg btn-wrapped" @click="createNewAid()" :loading="updating">
          <i class="pe-7s-plus"></i>
          {{ $t('Create New Access Identifier') }}
        </FormButton>
      </div>
    </div>
  </div>
  <LoadingCover :loading="!ready">
    <AccessIdsList :items="list" v-if="ready" />
  </LoadingCover>
</template>

<script setup>
  import {useAccessIds} from '@/stores/access-ids-store';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import AccessIdsList from '@/access-ids/access-ids-list.vue';
  import {storeToRefs} from 'pinia';
  import FormButton from '@/common/gui/FormButton.vue';
  import {useRouter} from 'vue-router';

  const router = useRouter();

  const aidsStore = useAccessIds();
  const {list, ready, updating} = storeToRefs(aidsStore);

  async function createNewAid() {
    const newItem = await aidsStore.create();
    await router.push({name: 'accessId', params: {id: newItem.id}});
  }
</script>

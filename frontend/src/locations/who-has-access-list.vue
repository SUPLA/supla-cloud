<template>
  <div>
    <h4 class="mt-3 text-center">{{ $t('Who has access?') }}</h4>
    <div v-if="accessIds.length > 0" class="list-group m-0">
      <router-link v-for="aid in accessIds" :key="aid.id" :to="{name: 'accessId', params: {id: aid.id}}" class="list-group-item">
        ID{{ aid.id }} {{ aid.caption }}
      </router-link>
    </div>
    <div v-else class="text-center">
      <fa :icon="faGhost" class="mr-1" />
      {{ $t('No one') }}
    </div>
  </div>
</template>

<script setup>
  import {computed} from 'vue';
  import {useAccessIds} from '@/stores/access-ids-store';
  import {faGhost} from '@fortawesome/free-solid-svg-icons';

  const props = defineProps({locationId: Number});

  const accessIdsStore = useAccessIds();
  const accessIds = computed(() => accessIdsStore.forLocation({id: props.locationId}));
</script>

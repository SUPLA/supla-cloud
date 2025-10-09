<template>
  <carousel-page
    header-i18n="Access Identifiers"
    :tile="AccessIdTile"
    :filters="AccessIdFilters"
    endpoint="accessids"
    :store="accessIdsStore"
    create-new-label-i18n="Create New Access Identifier"
    :limit="userData.limits.accessId"
    details-route="accessId"
    breadcrumbs
  ></carousel-page>
</template>

<script setup>
  import CarouselPage from '../common/pages/carousel-page.vue';
  import AccessIdTile from './access-id-tile.vue';
  import AccessIdFilters from './access-id-filters.vue';
  import {storeToRefs} from 'pinia';
  import {useCurrentUserStore} from '@/stores/current-user-store';
  import {useAccessIdsStore} from '@/stores/access-ids-store';

  const {userData} = storeToRefs(useCurrentUserStore());
  const accessIdsStore = useAccessIdsStore();
</script>

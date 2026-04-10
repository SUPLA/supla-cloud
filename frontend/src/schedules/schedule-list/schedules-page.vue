<template>
  <carousel-page
    header-i18n="Schedules"
    :tile="ScheduleTile"
    endpoint="schedules?include=subject,closestExecutions"
    create-new-label-i18n="Create New Schedule"
    :limit="userData.limits.schedule"
    :filters="ScheduleFilters"
    details-route="schedule"
  ></carousel-page>
</template>

<script setup>
  import CarouselPage from '../../common/pages/carousel-page.vue';
  import ScheduleTile from './schedule-tile.vue';
  import ScheduleFilters from './schedule-filters.vue';
  import {useCurrentUserStore} from '@/stores/current-user-store';
  import {storeToRefs} from 'pinia';

  const {userData} = storeToRefs(useCurrentUserStore());
</script>

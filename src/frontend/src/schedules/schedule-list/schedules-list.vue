<template>
  <list-page
    header-i18n="Schedules"
    :tile="ScheduleTile"
    :filters="ScheduleFilters"
    endpoint="schedules?include=subject,closestExecutions"
    create-new-label-i18n="Create New Schedule"
    :limit="userData.limits.schedule"
    details-route="schedule"
    :subject="subject"
  ></list-page>
</template>

<script setup>
  import ScheduleTile from './schedule-tile.vue';
  import ScheduleFilters from './schedule-filters.vue';
  import ListPage from '../../common/pages/list-page.vue';
  import {storeToRefs} from 'pinia';
  import {useCurrentUserStore} from '@/stores/current-user-store';

  defineProps({subject: Object});

  const {userData} = storeToRefs(useCurrentUserStore());
</script>

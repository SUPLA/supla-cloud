<template>
  <list-page
    header-i18n="Notifications"
    :tile="NotificationTile"
    subtitle-i18n="This is the list of configured notifications. If you want to add a new notification or alter the existing one, go to the channel details that should trigger it."
    endpoint="notifications"
  />
</template>

<script setup>
  import ListPage from '@/common/pages/list-page.vue';
  import NotificationTile from '@/notifications/notification-tile.vue';
  import {useScenesStore} from '@/stores/scenes-store.js';
  import {useReactionsStore} from '@/stores/reactions-store.js';

  useScenesStore().fetchAll();
  useReactionsStore().fetchAll();
</script>

<template>
  <div class="container">
    <h1 class="m-0" v-title>{{ $t('Notifications') }}</h1>
    <h4>
      {{
        $t(
          'This is the list of configured notifications. If you want to add a new notification or alter the existing one, go to the channel details that should trigger it.'
        )
      }}
    </h4>
  </div>
  <LoadingCover :loading="!ready">
    <SquareLinksList :items="list" :total="list.length">
      <template #item="{item}">
        <NotificationTile :model="item" />
      </template>
    </SquareLinksList>
  </LoadingCover>
</template>

<script setup>
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {storeToRefs} from 'pinia';
  import {useNotifications} from '@/stores/notifications-store.js';
  import SquareLinksList from '@/common/list/square-links-list.vue';
  import NotificationTile from '@/notifications/notification-tile.vue';

  const store = useNotifications({force: true});
  const {list, ready} = storeToRefs(store);
</script>

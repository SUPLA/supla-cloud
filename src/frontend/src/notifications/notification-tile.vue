<template>
  <square-link class="clearfix pointer lift-up green" @click="$emit('click')">
    <router-link :to="linkSpec">
      <h3 class="no-margin-top line-clamp line-clamp-4">{{ $t(notificationTypeLabels[notificationType]) }}</h3>
      <div class="line-clamp line-clamp-4">{{ model.title }}</div>
      <div class="line-clamp line-clamp-4">{{ model.body }}</div>
      <h5>{{ $t('Recipients') }}</h5>
      <ul>
        <li v-for="aid in model.accessIdsIds" :key="aid.id">{{ accessIds[aid].caption }}</li>
      </ul>
    </router-link>
  </square-link>
</template>

<script setup>
  import SquareLink from '@/common/tiles/square-link.vue';
  import {computed} from 'vue';
  import {storeToRefs} from 'pinia';
  import {useChannelsStore} from '@/stores/channels-store.js';
  import ChannelFunction from '@/common/enums/channel-function.js';
  import {useReactionsStore} from '@/stores/reactions-store.js';
  import {useAccessIdsStore} from '@/stores/access-ids-store.js';

  const props = defineProps({model: Object});

  const {all: channels} = storeToRefs(useChannelsStore());
  const {all: accessIds} = storeToRefs(useAccessIdsStore());

  const notificationTypeLabels = {
    scene: 'Scene notification', // i18n
    device: 'Device notification', // i18n
    reaction: 'Reaction notification', // i18n
    actionTrigger: 'Action trigger notification', // i18n
    channel: 'Channel notification', // i18n
  };

  const notificationType = computed(() => {
    if (props.model.subjectType === 'scene') {
      return 'scene';
    } else if (props.model.subjectType === 'device') {
      return 'device';
    } else if (props.model.subjectType === 'reaction') {
      return 'reaction';
    } else {
      const channel = channels.value[props.model.subjectId];
      return channel.functionId === ChannelFunction.ACTION_TRIGGER ? 'actionTrigger' : 'channel';
    }
  });

  const linkSpec = computed(() => {
    switch (notificationType.value) {
      case 'scene':
        return {name: 'scene', params: {id: props.model.subjectId}};
      case 'device':
        return {name: 'device.notifications', params: {id: props.model.subjectId}};
      case 'reaction': {
        const reaction = useReactionsStore().all[props.model.subjectId];
        return {name: 'channel.reactions.details', params: {id: reaction.owningChannelId, reactionId: props.model.subjectId}};
      }
      case 'actionTrigger': {
        const channel = channels.value[props.model.subjectId];
        if (channel?.config.relatedChannelId) {
          return {name: 'channel.actionTriggers', params: {id: channel.config.relatedChannelId}};
        } else {
          return {name: 'channel.actionTriggers', params: {id: props.model.subjectId}};
        }
      }
      default:
        return {name: 'channel.notifications', params: {id: props.model.subjectId}};
    }
  });
</script>

<style lang="scss" scoped>
  ul {
    list-style-type: none;
    padding: 0;
  }
</style>

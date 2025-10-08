<template>
  <div class="channel-reactions-config container">
    <CarouselPage
      header-i18n="Reactions"
      dont-set-page-title
      :tile="ReactionTile"
      :endpoint="`channels/${subject.id}/reactions?include=subject,owningChannel`"
      create-new-label-i18n="Create new reaction"
      list-route="channel.reactions"
      details-route="channel.reactions.details"
      id-param-name="reactionId"
      :limit="userData.limits.schedule"
      :new-item-factory="newReactionFactory"
    />
  </div>
</template>

<script setup>
  import ReactionTile from './reaction-tile.vue';
  import CarouselPage from '@/common/pages/carousel-page.vue';
  import {storeToRefs} from 'pinia';
  import {useCurrentUserStore} from '@/stores/current-user-store';

  const props = defineProps({subject: Object});

  function newReactionFactory() {
    return {
      owningChannel: props.subject,
      enabled: true,
    };
  }

  const {userData} = storeToRefs(useCurrentUserStore());
</script>

<style lang="scss">
  .channel-reactions-config {
    min-height: 850px;
    .owning-channel-caption {
      display: none;
    }
  }
</style>

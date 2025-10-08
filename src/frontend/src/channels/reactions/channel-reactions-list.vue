<template>
  <list-page
    header-i18n="Reactions"
    :tile="ReactionTile"
    subtitle-i18n="This is the list of configured reactions. If you want to add a new reaction, go to the channel details that should trigger it."
    endpoint="reactions?include=owningChannel,subject"
  />
</template>

<script setup>
  import ListPage from '@/common/pages/list-page.vue';
  import ReactionTile from '@/channels/reactions/reaction-tile.vue';
</script>

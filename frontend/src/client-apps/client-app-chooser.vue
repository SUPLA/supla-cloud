<template>
  <square-modal-chooser
    title-i18n="Choose client apps"
    endpoint="client-apps?include=accessId"
    :tile="ClientAppTile"
    :filters="ClientAppFilters"
    :selected="selected"
    @select="$emit('select', $event)"
    @cancel="$emit('cancel', $event)"
    @confirm="$emit('confirm', $event)"
  ></square-modal-chooser>
</template>

<script setup>
  import ClientAppTile from './client-app-tile.vue';
  import ClientAppFilters from './client-app-filters.vue';
  import SquareModalChooser from '../common/tiles/square-modal-chooser.vue';

  defineProps(['selected']);
</script>

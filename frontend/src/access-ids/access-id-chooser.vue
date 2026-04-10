<template>
  <square-modal-chooser
    :title-i18n="titleI18n"
    endpoint="accessids?include=password"
    :tile="AccessIdTile"
    :filters="AccessIdFilters"
    :selected="selected"
    @select="$emit('select', $event)"
    @cancel="$emit('cancel', $event)"
    @confirm="$emit('confirm', $event)"
  ></square-modal-chooser>
</template>

<script setup>
  import AccessIdTile from './access-id-tile.vue';
  import AccessIdFilters from './access-id-filters.vue';
  import SquareModalChooser from '../common/tiles/square-modal-chooser.vue';

  defineProps(['selected', 'titleI18n']);
</script>

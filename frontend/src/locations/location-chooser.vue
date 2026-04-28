<template>
  <square-modal-chooser
    title-i18n="Choose location"
    endpoint="locations"
    :tile="LocationTile"
    :filters="LocationFilters"
    :selected="selected"
    @select="$emit('select', $event)"
    @cancel="$emit('cancel', $event)"
    @confirm="$emit('confirm', $event)"
  ></square-modal-chooser>
</template>

<script setup>
  import LocationTile from './location-tile.vue';
  import LocationFilters from './location-filters.vue';
  import SquareModalChooser from '../common/tiles/square-modal-chooser.vue';

  defineProps(['selected']);
</script>

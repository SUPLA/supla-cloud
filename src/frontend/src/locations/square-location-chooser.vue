<template>
  <div>
    <square-link v-if="value" :class="'text-left with-label ' + squareLinkClass + ' ' + (value.enabled ? '' : 'grey')" @click="chooseLocation = !disabled">
      <location-tile-content :location="value"></location-tile-content>
    </square-link>
    <button v-else class="btn btn-default" type="button" @click="chooseLocation = !disabled">
      {{ $t('choose') }}
    </button>
    <location-chooser
      v-if="chooseLocation"
      :selected="value"
      class="text-left"
      @confirm="onLocationChange($event)"
      @cancel="chooseLocation = false"
    ></location-chooser>
  </div>
</template>

<script>
  import LocationTileContent from './location-tile-content.vue';
  import LocationChooser from './location-chooser.vue';
  import SquareLink from '@/common/tiles/square-link.vue';

  export default {
    components: {SquareLink, LocationChooser, LocationTileContent},
    props: ['value', 'squareLinkClass', 'disabled'],
    data() {
      return {
        chooseLocation: false,
      };
    },
    methods: {
      onLocationChange(location) {
        this.chooseLocation = false;
        this.$emit('chosen', location);
      },
    },
  };
</script>

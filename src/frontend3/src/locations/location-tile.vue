<template>
    <square-link :class="'clearfix pointer with-label lift-up ' + (model.enabled ? '' : 'grey ')"
        @click="$emit('click')">
        <router-link :to="linkSpec">
            <location-tile-content :location="model"></location-tile-content>
        </router-link>
    </square-link>
</template>

<script>
  import LocationTileContent from "./location-tile-content.vue";
  import SquareLink from "@/common/tiles/square-link.vue";

  export default {
        components: {SquareLink, LocationTileContent},
        props: ['model', 'noLink'],
        computed: {
            linkSpec() {
                return this.noLink ? {} : {name: 'location', params: {id: this.model.id}};
            }
        }
    };
</script>

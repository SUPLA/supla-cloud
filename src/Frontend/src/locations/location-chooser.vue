<template>
    <square-modal-chooser
        title-i18n="Choose location"
        endpoint="locations"
        tile="location-tile"
        filters="location-filters"
        :selected="selected"
        @select="$emit('select', $event)"
        @cancel="$emit('cancel', $event)"
        @confirm="$emit('confirm', $event)"></square-modal-chooser>
</template>

<script>
    import LocationTile from "./location-tile";
    import LocationFilters from "./location-filters";
    import Vue from "vue";
    import SquareModalChooser from "../common/tiles/square-modal-chooser";

    Vue.component('LocationTile', LocationTile);
    Vue.component('LocationFilters', LocationFilters);

    export default {
        components: {SquareModalChooser},
        props: ['selected'],
    };
</script>

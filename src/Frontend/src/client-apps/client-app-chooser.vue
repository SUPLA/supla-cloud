<template>
    <square-modal-chooser
        title="Choose client apps"
        endpoint="client-apps?include=accessId"
        tile="client-app-tile"
        filters="client-app-filters"
        :selected="selected"
        :square-links-height="240"
        @select="$emit('select', $event)"
        @cancel="$emit('cancel', $event)"
        @confirm="$emit('confirm', $event)"></square-modal-chooser>
</template>

<script>
    import ClientAppTile from "./client-app-tile";
    import ClientAppFilters from "./client-app-filters";
    import Vue from "vue";
    import SquareModalChooser from "../common/tiles/square-modal-chooser";

    Vue.component('ClientAppTile', ClientAppTile);
    Vue.component('ClientAppFilters', ClientAppFilters);

    export default {
        components: {SquareModalChooser},
        props: ['selected'],
    };
</script>

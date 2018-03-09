<template>
    <square-modal-chooser
        title="Choose a channel group"
        endpoint="channel-groups"
        tile="channel-group-tile"
        filters="channel-group-filters"
        :selected="selected"
        @select="$emit('select', $event)"
        @cancel="$emit('cancel', $event)"
        @confirm="$emit('confirm', $event)"></square-modal-chooser>
</template>

<script>
    import ChannelGroupTile from "./channel-group-tile";
    import ChannelGroupFilters from "./channel-group-filters";
    import Vue from "vue";
    import SquareModalChooser from "../common/tiles/square-modal-chooser";

    Vue.component('ChannelGroupTile', ChannelGroupTile);
    Vue.component('ChannelGroupFilters', ChannelGroupFilters);

    export default {
        components: {SquareModalChooser},
        props: ['selected'],
    };
</script>

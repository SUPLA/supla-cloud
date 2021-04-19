<template>
    <square-modal-chooser
        :title-i18n="titleI18n"
        endpoint="accessids?include=password"
        tile="access-id-tile"
        filters="access-id-filters"
        :selected="selected"
        @select="$emit('select', $event)"
        @cancel="$emit('cancel', $event)"
        @confirm="$emit('confirm', $event)"></square-modal-chooser>
</template>

<script>
    import AccessIdTile from "./access-id-tile";
    import AccessIdFilters from "./access-id-filters";
    import Vue from "vue";
    import SquareModalChooser from "../common/tiles/square-modal-chooser";

    Vue.component('AccessIdTile', AccessIdTile);
    Vue.component('AccessIdFilters', AccessIdFilters);

    export default {
        components: {SquareModalChooser},
        props: ['selected', 'titleI18n'],
    };
</script>

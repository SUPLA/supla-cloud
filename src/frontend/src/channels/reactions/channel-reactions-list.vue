<template>
    <list-page header-i18n="Reactions"
        tile="reaction-tile"
        endpoint="reactions?include=owningChannel,subject"
    />
</template>

<script>
    import Vue from "vue";
    import ListPage from "@/common/pages/list-page";
    import ReactionTile from "@/channels/reactions/reaction-tile.vue";

    Vue.component('ReactionTile', ReactionTile);

    export default {
        components: {ListPage},
    };
</script>

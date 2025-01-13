<template>
    <list-page header-i18n="Scenes"
        tile="scene-tile"
        filters="scene-filters"
        endpoint="scenes"
        create-new-label-i18n="Create new scene"
        :limit="userData.limits.scene"
        details-route="scene"
        :subject="subject"></list-page>
</template>

<script>
    import SceneTile from "./scene-tile";
    import SceneFilters from "./scene-filters";
    import Vue from "vue";
    import ListPage from "../common/pages/list-page";
    import {mapState} from "pinia";
    import {useCurrentUserStore} from "@/stores/current-user-store";

    Vue.component('SceneTile', SceneTile);
    Vue.component('SceneFilters', SceneFilters);

    export default {
        props: ['subject'],
        components: {ListPage},
        computed: {
            ...mapState(useCurrentUserStore, ['userData']),
        },
    };
</script>

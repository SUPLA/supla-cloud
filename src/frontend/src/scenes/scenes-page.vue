<template>
    <CarouselPage header-i18n="Scenes"
        tile="scene-tile"
        endpoint="scenes"
        create-new-label-i18n="Create new scene"
        :limit="userData.limits.scene"
        filters="scene-filters"
        details-route="scene"/>
</template>

<script setup>
    import CarouselPage from "../common/pages/carousel-page";
    import SceneTile from "./scene-tile";
    import SceneFilters from "./scene-filters";
    import Vue from "vue";
    import {useCurrentUserStore} from "@/stores/current-user-store";

    Vue.component('SceneTile', SceneTile);
    Vue.component('SceneFilters', SceneFilters);

    const {userData} = useCurrentUserStore();
</script>

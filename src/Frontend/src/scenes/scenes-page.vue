<template>
    <carousel-page header-i18n="Scenes"
        tile="scene-tile"
        endpoint="scenes"
        create-new-label-i18n="Create new scene"
        :limit="$user.userData.limits.scene"
        details-route="scene"></carousel-page>
</template>

<script>
    import CarouselPage from "../common/pages/carousel-page";
    import SceneTile from "./scene-tile";
    import Vue from "vue";

    Vue.component('SceneTile', SceneTile);

    export default {
        components: {CarouselPage}
    };
</script>

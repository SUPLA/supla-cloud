<template>
    <CarouselPage header-i18n="Scenes"
        :tile="SceneTile"
        endpoint="scenes"
        create-new-label-i18n="Create new scene"
        :limit="userData.limits.scene"
        :filters="SceneFilters"
        details-route="scene"/>
</template>

<script setup>
  import CarouselPage from "../common/pages/carousel-page.vue";
  import SceneTile from "./scene-tile.vue";
  import SceneFilters from "./scene-filters.vue";
  import {useCurrentUserStore} from "@/stores/current-user-store";
  import {storeToRefs} from "pinia";

  const {userData} = storeToRefs(useCurrentUserStore());
</script>

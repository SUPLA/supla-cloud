<template>
    <list-page header-i18n="Scenes"
        :tile="SceneTile"
        :filters="SceneFilters"
        endpoint="scenes"
        create-new-label-i18n="Create new scene"
        :limit="userData.limits.scene"
        details-route="scene"
        :subject="subject"></list-page>
</template>

<script setup>
  import SceneTile from "./scene-tile.vue";
  import SceneFilters from "./scene-filters.vue";
  import ListPage from "../common/pages/list-page.vue";
  import {storeToRefs} from "pinia";
  import {useCurrentUserStore} from "@/stores/current-user-store";

  defineProps({subject: Object});

    const {userData} = storeToRefs(useCurrentUserStore());
</script>

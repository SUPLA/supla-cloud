<template>
    <div class="h-100">
        <div class="page-content">
            <!--            <transition name="fade">-->
            <!--                <navbar v-if="currentUser.username"></navbar>-->
            <!--            </transition>-->
            <!--            <div class="alert alert-warning maintenance-warning"-->
            <!--                v-if="frontendConfig.config.maintenanceMode && currentUser.username">-->
            <!--                <maintenance-warning></maintenance-warning>-->
            <!--            </div>-->
            <!--            <LoadingCover :loading="$changingRoute">-->
            <RouterView/>
            <!--            </LoadingCover>-->
          <cookie-warning
            v-if="frontendConfig.config.requireCookiePolicyAcceptance && currentUser.username && !currentUser.userData.agreements.cookies"></cookie-warning>
            <!--            <cloud-version-mismatch-warning-modal></cloud-version-mismatch-warning-modal>-->
        </div>
        <PageFooter :username="currentUser.username"/>
    </div>
</template>

<script setup>
  import {useFrontendConfigStore} from "@/stores/frontend-config-store";
  import {useCurrentUserStore} from "@/stores/current-user-store";
  import PageFooter from "@/common/gui/page-footer.vue";
  import {onMounted} from "vue";
  import CookieWarning from "@/common/errors/cookie-warning.vue";

  const frontendConfig = useFrontendConfigStore();
    const currentUser = useCurrentUserStore();

    onMounted(() => {
      document.getElementById('page-preloader').remove();
      document.getElementById('vue-container')?.classList.remove('hidden');
    })
</script>

<style>
    .h-100 {
        height: 100%;
    }
</style>

<template>
    <div class="h-100">
        <div class="page-content">
          <transition name="fade">
            <Navbar v-if="currentUser.username"/>
          </transition>
                        <div class="alert alert-warning maintenance-warning"
                            v-if="frontendConfig.config.maintenanceMode && currentUser.username">
                            <maintenance-warning></maintenance-warning>
                        </div>
          <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in" :duration="100">
              <component :is="Component" />
            </transition>
          </router-view>
          <cookie-warning
            v-if="frontendConfig.config.requireCookiePolicyAcceptance && currentUser.username && !currentUser.userData.agreements.cookies"></cookie-warning>
                        <cloud-version-mismatch-warning-modal/>
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
  import Navbar from "@/home/navbar.vue";
  import CloudVersionMismatchWarningModal
    from "@/common/errors/cloud-version-mismatch-warning-modal.vue";
  import MaintenanceWarning from "@/common/errors/maintenance-warning.vue";

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

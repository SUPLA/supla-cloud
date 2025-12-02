<template>
  <div class="h-100">
    <div class="page-content">
      <transition name="fade">
        <Navbar v-if="showNavbar" />
      </transition>
      <div v-if="frontendConfig.config.maintenanceMode && currentUser.username" class="alert alert-warning maintenance-warning">
        <maintenance-warning></maintenance-warning>
      </div>
      <TechnicalAccessWarning v-if="currentUser.username && currentUser.technicalAccess" />
      <router-view v-slot="{Component}">
        <transition name="fade" mode="out-in" :duration="100">
          <component :is="Component" />
        </transition>
      </router-view>
      <cookie-warning
        v-if="frontendConfig.config.requireCookiePolicyAcceptance && currentUser.username && !currentUser.userData.agreements.cookies"
      ></cookie-warning>
      <cloud-version-mismatch-warning-modal v-if="currentUser.username" />
    </div>
    <PageFooter :username="currentUser.username" />
  </div>
</template>

<script setup>
  import {useFrontendConfigStore} from '@/stores/frontend-config-store';
  import {useCurrentUserStore} from '@/stores/current-user-store';
  import PageFooter from '@/common/gui/page-footer.vue';
  import {computed, onMounted} from 'vue';
  import CookieWarning from '@/common/errors/cookie-warning.vue';
  import Navbar from '@/home/navbar.vue';
  import CloudVersionMismatchWarningModal from '@/common/errors/cloud-version-mismatch-warning-modal.vue';
  import MaintenanceWarning from '@/common/errors/maintenance-warning.vue';
  import TechnicalAccessWarning from '@/common/errors/technical-access-warning.vue';
  import {useRoute} from 'vue-router';

  const frontendConfig = useFrontendConfigStore();
  const currentUser = useCurrentUserStore();
  const route = useRoute();

  const showNavbar = computed(() => currentUser.username && !['directLinkExecution'].includes(route.name));

  onMounted(() => {
    document.getElementById('page-preloader').remove();
    document.getElementById('vue-container')?.classList.remove('hidden');
  });
</script>

<style>
  .h-100 {
    height: 100%;
  }
</style>

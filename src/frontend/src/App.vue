<template>
    <div class="h-100">
        <div class="page-content">
            <transition name="fade">
                <navbar v-if="$user.username"></navbar>
            </transition>
            <div class="alert alert-warning maintenance-warning"
                v-if="frontendConfig.config.maintenanceMode && $user.username">
                <maintenance-warning></maintenance-warning>
            </div>
            <loading-cover :loading="$changingRoute">
                <router-view></router-view>
            </loading-cover>
            <cookie-warning
                v-if="frontendConfig.config.requireCookiePolicyAcceptance && $user.username && !$user.userData.agreements.cookies"></cookie-warning>
            <cloud-version-mismatch-warning-modal></cloud-version-mismatch-warning-modal>
        </div>
        <page-footer :username="$user.username"></page-footer>
    </div>
</template>

<script setup>
    import PageFooter from "./common/gui/page-footer";
    import Navbar from "./home/navbar";
    import MaintenanceWarning from "./common/errors/maintenance-warning";
    import CookieWarning from "./common/errors/cookie-warning";
    import CloudVersionMismatchWarningModal from "./common/errors/cloud-version-mismatch-warning-modal";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    const frontendConfig = useFrontendConfigStore();
</script>

<style>
    .h-100 {
        height: 100%;
    }
</style>

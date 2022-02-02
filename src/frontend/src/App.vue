<template>
    <div class="h-100">
        <div class="page-content">
            <transition name="fade">
                <navbar v-if="$user.username"></navbar>
            </transition>
            <div class="alert alert-warning maintenance-warning"
                v-if="$frontendConfig.maintenanceMode && $user.username">
                <maintenance-warning></maintenance-warning>
            </div>
            <loading-cover :loading="$changingRoute">
                <transition name="fade-router">
                    <router-view></router-view>
                </transition>
            </loading-cover>
            <cookie-warning v-if="$frontendConfig.requireCookiePolicyAcceptance && $user.username && !$user.userData.agreements.cookies"></cookie-warning>
            <cloud-version-mismatch-warning-modal></cloud-version-mismatch-warning-modal>
        </div>
        <page-footer :username="$user.username"></page-footer>
    </div>
</template>

<script>
    import PageFooter from "./common/gui/page-footer";
    import Navbar from "./home/navbar";
    import MaintenanceWarning from "./common/errors/maintenance-warning";
    import CookieWarning from "./common/errors/cookie-warning";
    import CloudVersionMismatchWarningModal from "./common/errors/cloud-version-mismatch-warning-modal";

    export default {
        components: {CookieWarning, MaintenanceWarning, Navbar, PageFooter, CloudVersionMismatchWarningModal}
    };
</script>

<style>
    .h-100 {
        height: 100%;
    }
</style>

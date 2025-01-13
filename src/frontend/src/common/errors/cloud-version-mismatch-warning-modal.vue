<template>
    <modal-confirm
        v-if="!frontendConfigStore.backendAndFrontendVersionMatches && !isDev && shown"
        @confirm="redirect()"
        @cancel="shown = false"
        class="modal-warning green-confirm-button"
        :header="$t('Update in progress')">
        <p>{{ $t('The SUPLA infrastructure is being updated.') }}</p>
        <p>{{ $t('For a short period of time, it is recommended for you to use the {suplaUrl} address.', {suplaUrl: frontendConfigStore.config.suplaUrl}) }}</p>
        <p>{{ $t('Do you want to be redirected there? You may be asked to authenticate again.') }}</p>
    </modal-confirm>
</template>

<script>
    import {mapStores} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        data() {
            return {
                shown: true,
            }
        },
        methods: {
            redirect() {
                window.location.href = this.frontendConfigStore.config.suplaUrl;
            }
        },
        computed: {
            isDev() {
                return ['dev', 'e2e'].includes(this.frontendConfigStore.env);
            },
            ...mapStores(useFrontendConfigStore),
        }
    };
</script>

<style scoped
    lang="scss">
    @import "../../styles/variables";

    .alert {
        width: 90%;
        max-width: 300px;
        margin: 0 auto;
        position: fixed;
        bottom: 45px;
        right: 5px;
        background: $supla-yellow;
        border-color: darken($supla-yellow, 10%);
    }
</style>

<style>
    .hide-cookies-warning .cookie-warning {
        display: none;
    }
</style>

<template>
    <modal-confirm
        v-if="shown"
        @confirm="redirect()"
        @cancel="shown = false"
        class="modal-warning green-confirm-button"
        :header="$t('Update in progress')">
        <p>{{ $t('The SUPLA infrastructure is being updated.') }}</p>
        <p>{{ $t('For a short period of time, it is recommended for you to use the {suplaUrl} address.', {suplaUrl: frontendConfig.suplaUrl}) }}</p>
        <p>{{ $t('Do you want to be redirected there? You may be asked to authenticate again.') }}</p>
    </modal-confirm>
</template>

<script>
    import EventBus from "../event-bus";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        data() {
            return {
                shown: false,
                callback: undefined,
            };
        },
        mounted() {
            this.callback = () => this.showWarningIfNeeded();
            EventBus.$on('backend-version-updated', this.callback);
            this.showWarningIfNeeded();
        },
        beforeDestroy() {
            EventBus.$off('backend-version-updated', this.callback);
        },
        methods: {
            showWarningIfNeeded() {
                this.shown = !this.$backendAndFrontendVersionMatches;
            },
            redirect() {
                window.location.href = this.frontendConfig.suplaUrl;
            }
        },
        computed: {
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
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

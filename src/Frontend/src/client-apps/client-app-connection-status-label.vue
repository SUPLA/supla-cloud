<template>
    <span :class="'label ' + (connected ? 'label-success' : 'label-grey')"
        v-if="connected !== undefined">
        {{ $t(connected ? 'Active' : 'Idle') }}
    </span>
</template>

<script>
    import clientAppConnectionMonitor from "./client-app-connection-monitor";

    export default {
        props: ['app'],
        data() {
            return {
                connected: undefined,
                callback: (newState) => this.connected = this.$set(this.app, 'connected', newState),
            };
        },
        mounted() {
            clientAppConnectionMonitor.register(this.app, this.callback);
        },
        beforeDestroy() {
            clientAppConnectionMonitor.unregister(this.app, this.callback);
        }
    };
</script>

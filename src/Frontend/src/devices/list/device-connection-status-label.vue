<template>
    <span :class="'label ' + (connected ? 'label-success' : 'label-danger')"
        v-if="connected !== undefined">
        {{ connected ? $t('Connected') : $t('Disconnected') }}
    </span>
</template>

<script>
    import deviceConnectionMonitor from "../device-connection-monitor";

    export default {
        props: ['device'],
        data() {
            return {
                connected: undefined,
                callback: (newState) => this.connected = this.$set(this.device, 'connected', newState),
            };
        },
        mounted() {
            deviceConnectionMonitor.register(this.device, this.callback);
        },
        beforeDestroy() {
            deviceConnectionMonitor.unregister(this.device, this.callback);
        }
    };
</script>

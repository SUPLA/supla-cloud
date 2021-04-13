<template>
    <span :class="'label ' + (connected ? 'label-success' : 'label-danger')"
        v-if="connected !== undefined">
        {{ connected ? $t('Connected') : $t('Disconnected') }}
    </span>
</template>

<script>
    import deviceConnectionMonitor from "../device-connection-monitor";
    import channelConnectionMonitor from "../../channels/channel-connection-monitor";

    export default {
        props: ['model'],
        data() {
            return {
                monitor: undefined,
                connected: undefined,
                callback: (newState) => this.connected = this.$set(this.model, 'connected', newState),
            };
        },
        mounted() {
            this.monitor = this.model.functionId !== undefined ? channelConnectionMonitor : deviceConnectionMonitor;
            this.monitor.register(this.model, this.callback);
        },
        beforeDestroy() {
            this.monitor.unregister(this.model, this.callback);
        }
    };
</script>

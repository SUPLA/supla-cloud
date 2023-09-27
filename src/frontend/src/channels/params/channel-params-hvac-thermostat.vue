<template>
    <div>
        <div class="alert alert-warning m-0 mt-3" v-if="channel.config.waitingForConfigInit">
            {{ $t('Configuration not available yet. Waiting for the device to connect.') }}
            <fa icon="circle-notch" spin></fa>
        </div>
    </div>
</template>

<script>
    import EventBus from "@/common/event-bus";

    export default {
        props: ['channel'],
        data() {
            return {
                configWaiterInterval: undefined,
            };
        },
        beforeMount() {
            if (this.channel.config.waitingForConfigInit) {
                this.configWaiterInterval = setInterval(
                    () => this.$http.get(`channels/${this.channel.id}`).then(response => {
                        if (!response.body.config?.waitingForConfigInit) {
                            clearInterval(this.configWaiterInterval);
                            EventBus.$emit('channel-updated');
                        }
                    }),
                    5000
                );
            }
        },
        beforeDestroy() {
            clearInterval(this.configWaiterInterval);
        }
    };
</script>

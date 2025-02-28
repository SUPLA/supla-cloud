<template>
    <div v-if="device.flags.identifyDeviceAvailable" v-tooltip="disabledReason">
        <button class="btn btn-default btn-block btn-wrapped" type="button"
            :disabled="!!disabledReason || loading"
            @click="identifyDevice()">
            <button-loading-dots v-if="loading"/>
            <span v-else>{{ $t('Identify device') }}</span>
        </button>
        <transition-expand>
            <div class="text-center mt-2" v-if="timer">
                {{ $t('Identify request sent.') }}
            </div>
        </transition-expand>
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {mapState} from "pinia";
    import {useDevicesStore} from "@/stores/devices-store";

    export default {
        components: {TransitionExpand},
        props: ['device'],
        data() {
            return {
                loading: false,
                timer: undefined,
                lastPairingResult: null,
            };
        },
        methods: {
            identifyDevice() {
                this.loading = true;
                clearTimeout(this.timer);
                this.$http.patch('iodevices/' + this.device.id, {action: 'identifyDevice'})
                    .then(() => this.timer = setTimeout(() => this.timer = undefined, 3000))
                    .finally(() => this.loading = false);
            },
        },
        computed: {
            ...mapState(useDevicesStore, {devices: 'all'}),
            isConnected() {
                return this.devices[this.device.id]?.connected;
            },
            disabledReason() {
                if (!this.isConnected) {
                    return this.$t('Device is disconnected.');
                } else if (this.device.hasPendingChanges) {
                    return this.$t('Save or discard configuration changes first.')
                } else {
                    return '';
                }
            }
        },
        beforeDestroy() {
            clearTimeout(this.timer);
        }
    };
</script>

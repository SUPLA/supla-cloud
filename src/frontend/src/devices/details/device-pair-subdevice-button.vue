<template>
    <div v-if="device.pairingSubdevicesAvailable"
        v-tooltip="disabledReason">
        <button class="btn btn-default btn-block btn-wrapped" type="button"
            :disabled="!!disabledReason || loading"
            @click="pairSubdevices()">
            <button-loading-dots v-if="loading"/>
            <span v-else>{{ $t('Pair new devices or sensors') }}</span>
        </button>
    </div>
</template>

<script>
    import {successNotification} from "@/common/notifier";

    export default {
        props: ['device'],
        data() {
            return {
                loading: false,
            };
        },
        methods: {
            pairSubdevices() {
                this.loading = true;
                this.$http.patch('iodevices/' + this.device.id, {action: 'pairSubdevice'})
                    .then(() => setTimeout(() => this.assumeEntered(), 2000))
                    .catch(() => this.loading = false);
            },
            assumeEntered() {
                this.loading = false;
                successNotification(this.$t('Successful'), this.$t('Device should be now in pairing mode.'));
            },
        },
        computed: {
            isConnected() {
                return !!this.device.connected;
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
    };
</script>

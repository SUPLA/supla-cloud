<template>
    <div v-if="device.enterConfigurationModeAvailable"
        v-tooltip="disabledReason">
        <button class="btn btn-default btn-block btn-wrapped"
            type="button"
            :disabled="!!disabledReason"
            @click="enterConfirm = true">
            {{ $t('Switch the device to configuration mode') }}
        </button>
        <modal-confirm v-if="enterConfirm"
            class="modal-warning"
            @confirm="enterConfigurationMode()"
            :loading="loading"
            @cancel="enterConfirm = false"
            :header="$t('Are you sure?')">
            <p>{{ $t('After the device enters the configuration mode, it will not be accessible on your account.') }}</p>
            <p>{{ $t('It will start to broadcast its own access point that is meant to be connected with your smartphone to configure its connection settings and/or update the firmware.') }}</p>
            <p>{{ $t('If you fail to connect this AP for several minutes, it will restart itself and use the last settings.') }}</p>
            <p v-if="device.sleepModeEnabled">
                <strong>{{ $t('The device might be in a sleep mode.') }}</strong>
                {{ $t('If the device is not awaken now, it will enter the configuration mode at the next wake up.') }}
            </p>
        </modal-confirm>
    </div>
</template>

<script>
    import {successNotification} from "@/common/notifier";
    import {mapState} from "pinia";
    import {useDevicesStore} from "@/stores/devices-store";

    export default {
        props: ['device'],
        data() {
            return {
                enterConfirm: false,
                loading: false,
            };
        },
        methods: {
            enterConfigurationMode() {
                this.loading = true;
                this.$http.patch('iodevices/' + this.device.id, {action: 'enterConfigurationMode'})
                    .then(() => setTimeout(() => this.assumeEntered(), 5000))
                    .catch(() => this.loading = false);
            },
            assumeEntered() {
                this.enterConfirm = false;
                this.loading = false;
                successNotification(this.$t('Successful'), this.$t('Device should be now in configuration mode.'));
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
    };
</script>

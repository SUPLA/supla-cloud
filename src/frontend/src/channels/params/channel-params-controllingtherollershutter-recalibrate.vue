<template>
    <div v-if="channel.config.recalibrateAvailable"
        v-tooltip="calibrationDisabledReason">
        <button class="btn btn-default btn-block"
            type="button"
            :disabled="!isConnected || channel.hasPendingChanges"
            @click="calibrateConfirm = true">
            {{ $t('Calibrate') }}
        </button>
        <modal-confirm v-if="calibrateConfirm"
            class="modal-warning"
            @confirm="calibrate()"
            :loading="calibrating"
            @cancel="calibrateConfirm = false"
            :header="$t('Are you sure?')">
            {{ $t('Confirm if you want to perform the roller shutter calibration.') }}
        </modal-confirm>
    </div>
</template>

<script>
    import {errorNotification, successNotification} from "@/common/notifier";
    import EventBus from "@/common/event-bus";

    export default {
        props: ['channel'],
        data() {
            return {
                calibrateConfirm: false,
                calibrating: false,
                calibrationError: false,
            };
        },
        methods: {
            calibrate() {
                this.calibrating = true;
                this.$http.patch('channels/' + this.channel.id + '/settings', {action: 'recalibrate'})
                    .then(() => setTimeout(() => this.assumeCalibrationFinished(), 5000))
                    .then(() => EventBus.$emit('channel-state-updated'))
                    .catch(() => {
                        this.calibrationError = true;
                        this.calibrating = false;
                    });
            },
            assumeCalibrationFinished() {
                this.calibrateConfirm = false;
                this.calibrating = false;
            },
        },
        computed: {
            isConnected() {
                return !this.channel.state || this.channel.state.connected;
            },
            notCalibrated() {
                return this.calibrating || (this.channel.state && this.channel.state.not_calibrated);
            },
            calibrationDisabledReason() {
                if (!this.isConnected) {
                    return this.$t('Channel is disconnected.');
                } else if (this.channel.hasPendingChanges) {
                    return this.$t('Save or discard configuration changes before calibration.')
                } else {
                    return '';
                }
            }
        },
        watch: {
            notCalibrated(newState, oldState) {
                if (!newState && oldState === true) {
                    if (this.calibrationError) {
                        errorNotification(this.$t('Error'), this.$t('Could not perform the calibration.'));
                        this.calibrationError = false;
                    } else {
                        successNotification(this.$t('Successful'), this.$t('Calibration successful.'));
                    }
                }
            }
        },
    };
</script>

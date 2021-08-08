<template>
    <div v-if="channel.config.recalibrateAvailable">
        <button class="btn btn-default btn-block"
            :disabled="!isConnected || isCalibrating"
            @click="calibrateConfirm = true">
            <span v-if="!isCalibrating">
                {{ $t('Calibrate') }}
            </span>
            <span v-else>
                <button-loading-dots></button-loading-dots>
                {{ $t('calibration in progress') }}
            </span>
        </button>
        <modal-confirm v-if="calibrateConfirm"
            class="modal-warning"
            @confirm="calibrate()"
            @cancel="calibrateConfirm = false"
            :header="$t('Are you sure?')">
            {{ $t('Confirm if you want to perform the channel calibration.') }}
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
                this.calibrateConfirm = false;
                this.calibrating = true;
                this.$http.patch('channels/' + this.channel.id + '/settings', {action: 'recalibrate'})
                    .then(() => setTimeout(() => this.calibrating = false, 5000))
                    .then(() => EventBus.$emit('channel-state-updated'))
                    .catch(() => {
                        this.calibrationError = true;
                        this.calibrating = false;
                    });
            },
        },
        computed: {
            isConnected() {
                return !this.channel.state || this.channel.state.connected;
            },
            isCalibrating() {
                return this.calibrating || (this.channel.state && this.channel.state.is_calibrating);
            },
        },
        watch: {
            isCalibrating(newState, oldState) {
                if (!newState && oldState === true) {
                    if (this.calibrationError) {
                        errorNotification(this.$t('Error'), this.$t('Could not calibrate the channel.'));
                        this.calibrationError = false;
                    } else {
                        successNotification(this.$t('Successful'), this.$t('Channel has been calibrated.'));
                    }
                }
            }
        },
    };
</script>

<template>
    <div v-if="device.config && device.config.automaticTimeSync === false"
        v-tooltip="disabledReason">
        <button class="btn btn-default btn-block btn-wrapped"
            type="button"
            :disabled="!!disabledReason"
            @click="setDeviceTime()">
            {{ $t('Set the device time') }}
        </button>
        <modal-confirm v-if="deviceTime"
            :header="$t('Set the device time')"
            :loading="settingDeviceTime"
            @cancel="deviceTime = undefined"
            @confirm="setDeviceTime(deviceTime)">
            <div class="form-group">
                <input type="datetime-local" v-model="deviceTime" class="form-control">
            </div>
        </modal-confirm>
    </div>
</template>

<script>
    import {successNotification} from "@/common/notifier";
    import {DateTime} from "luxon";
    import {mapState} from "pinia";
    import {useDevicesStore} from "@/stores/devices-store";

    export default {
        props: {
            device: Object,
        },
        data() {
            return {
                deviceTime: undefined,
                settingDeviceTime: false,
            };
        },
        methods: {
            setDeviceTime(deviceTime) {
                if (deviceTime) {
                    const time = DateTime.fromISO(deviceTime).startOf('second').toISO({suppressMilliseconds: true});
                    this.settingDeviceTime = true;
                    this.$http.patch(`iodevices/${this.device.id}`, {action: 'setTime', time}).then(() => {
                        this.deviceTime = undefined;
                        successNotification(this.$t('Success'), this.$t('Device time has been set.'));
                    }).finally(() => this.settingDeviceTime = false);
                } else {
                    this.deviceTime = DateTime.now()
                        .startOf('second')
                        .toISO({includeOffset: false, suppressMilliseconds: true});
                }
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

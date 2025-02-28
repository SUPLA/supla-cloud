<template>
    <div v-if="device.flags.pairingSubdevicesAvailable" v-tooltip="disabledReason">
        <button class="btn btn-default btn-block btn-wrapped" type="button"
            :disabled="!!disabledReason || loading"
            @click="pairSubdevices()">
            <button-loading-dots v-if="loading"/>
            <span v-else>{{ $t('Pair new devices or sensors') }}</span>
        </button>
        <transition-expand>
            <div class="text-center mt-2" v-if="(loading && timer) || lastPairingResult">
                <div v-if="!lastPairingResult" class="small">
                    {{ $t('Pairing request sent.') }}
                </div>
                <div v-else>
                    <div class="small">
                        <span v-if="lastPairingResult.result === 'SUCCESS' && lastPairingResult.timedOut">
                            {{ $t('Last paired device') }}<br>
                            {{ lastPairingResult.time | formatDateTime }}
                        </span>
                        <span v-else>
                            {{ $t(`subdevicePairingResult_${lastPairingResult.result}`, {resultNum: lastPairingResult.resultNum}) }}
                        </span>
                    </div>
                    <div v-if="lastPairingResult.name">{{ lastPairingResult.name }}</div>
                </div>
            </div>
        </transition-expand>
        <!-- i18n: ['subdevicePairingResult_NOT_SUPPORTED', 'subdevicePairingResult_UNAUTHORIZED', 'subdevicePairingResult_ONGOING'] -->
        <!-- i18n: ['subdevicePairingResult_CMD_RESULT_UNKNOWN', 'subdevicePairingResult_PROCEDURE_STARTED', 'subdevicePairingResult_NO_NEW_DEVICE_FOUND'] -->
        <!-- i18n: ['subdevicePairingResult_SUCCESS', 'subdevicePairingResult_RESOURCES_LIMIT_EXCEEDED', 'subdevicePairingResult_NOT_STARTED_BUSY'] -->
        <!-- i18n: ['subdevicePairingResult_PAIRING_RESULT_UNKNOWN', 'subdevicePairingResult_NOT_STARTED_NOT_READY'] -->
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
                loading: true,
                timer: undefined,
                lastPairingResult: null,
            };
        },
        mounted() {
            this.fetchState().then(() => {
                if (this.lastPairingResult?.timedOut && this.lastPairingResult?.result !== 'SUCCESS') {
                    this.lastPairingResult = null;
                }
            });
        },
        methods: {
            pairSubdevices() {
                this.loading = true;
                this.lastPairingResult = null;
                clearTimeout(this.timer);
                this.$http.patch('iodevices/' + this.device.id, {action: 'pairSubdevice'})
                    .then(() => this.timer = setTimeout(() => this.fetchState(), 3000))
                    .catch(() => this.loading = false);
            },
            fetchState() {
                return this.$http.get(`iodevices/${this.device.id}?include=pairingResult`, {skipErrorHandler: true})
                    .then((response) => {
                        const pr = response.body.pairingResult;
                        // const pr = {"time": "2024-07-02T12:34:56Z", "result": "SUCCESS", "name": "ZAMEL", timedOut: true};
                        // const pr = {"time": "2024-07-02T12:34:56Z", "result": "PROCEDURE_STARTED", "name": "ZAMEL", timedOut: false};
                        if (pr?.result.startsWith('CMD_RESULT_')) {
                            pr.resultNum = pr.result.substring('CMD_RESULT_'.length);
                            pr.result = 'CMD_RESULT_UNKNOWN';
                        }
                        if (pr?.result.startsWith('PAIRING_RESULT_')) {
                            pr.resultNum = pr.result.substring('PAIRING_RESULT_'.length);
                            pr.result = 'PAIRING_RESULT_UNKNOWN';
                        }
                        this.lastPairingResult = pr;
                        if (!pr || !['PROCEDURE_STARTED', 'ONGOING'].includes(pr.result) || pr.timedOut) {
                            this.loading = false;
                        } else {
                            this.timer = setTimeout(() => this.fetchState(), 3000);
                        }
                    });
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

<template>
    <div v-if="device.pairingSubdevicesAvailable" v-tooltip="disabledReason">
        <button class="btn btn-default btn-block btn-wrapped" type="button"
            :disabled="!!disabledReason || loading"
            @click="pairSubdevices()">
            <button-loading-dots v-if="loading"/>
            <span v-else>{{ $t('Pair new devices or sensors') }}</span>
        </button>
        <transition-expand>
            <div class="text-center mt-2" v-if="loading || lastPairingResult">
                <div v-if="!lastPairingResult" class="small">
                    {{ $t('Pairing request sent.') }}
                </div>
                <div v-else>
                    <div class="small">
                        {{ $t(`subdevicePairingResult_${lastPairingResult.result}`, {resultNum: lastPairingResult.resultNum}) }}
                    </div>
                    <div v-if="lastPairingResult.name">{{ lastPairingResult.name }}</div>
                </div>
            </div>
        </transition-expand>
        <!-- i18n: ['subdevicePairingResult_NOT_SUPPORTED', 'subdevicePairingResult_UNAUTHORIZED', 'subdevicePairingResult_ONGOING'] -->
        <!-- i18n: ['subdevicePairingResult_CMD_RESULT_UNKNOWN', 'subdevicePairingResult_PROCEDURE_STARTED', 'subdevicePairingResult_NO_NEW_DEVICE_FOUND'] -->
        <!-- i18n: ['subdevicePairingResult_SUCCESS', 'subdevicePairingResult_RESOURCES_LIMIT_EXCEEDED', 'subdevicePairingResult_NOT_STARTED_BUSY'] -->
        <!-- i18n: ['subdevicePairingResult_PAIRING_RESULT_UNKNOWN'] -->
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand.vue";

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
            pairSubdevices() {
                this.loading = true;
                this.lastPairingResult = null;
                clearTimeout(this.timer);
                this.$http.patch('iodevices/' + this.device.id, {action: 'pairSubdevice'})
                    .then(() => this.timer = setTimeout(() => this.fetchState(), 3000))
                    .catch(() => this.loading = false);
            },
            fetchState() {
                this.$http.get(`iodevices/${this.device.id}?include=pairingResult`, {skipErrorHandler: true})
                    .then((response) => {
                        const pr = response.body.pairingResult;
                        // const pr = {"time": "2024-07-02T12:34:56Z", "result": "NO_NEW_DEVICE_FOUND"};
                        if (pr?.result.startsWith('CMD_RESULT_')) {
                            pr.resultNum = pr.result.substring('CMD_RESULT_'.length);
                            pr.result = 'CMD_RESULT_UNKNOWN';
                        }
                        if (pr?.result.startsWith('PAIRING_RESULT_')) {
                            pr.resultNum = pr.result.substring('PAIRING_RESULT_'.length);
                            pr.result = 'PAIRING_RESULT_UNKNOWN';
                        }
                        this.lastPairingResult = pr;
                        if (pr && !['PROCEDURE_STARTED', 'ONGOING'].includes(pr.result)) {
                            this.loading = false;
                            this.timer = setTimeout(() => this.lastPairingResult = null, 5000);
                        }
                    });
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
        beforeDestroy() {
            clearTimeout(this.timer);
        }
    };
</script>

<template>
    <div v-if="device.flags.pairingSubdevicesAvailable" class="d-flex align-items-center">
        <div class="text-center mr-2" v-if="lastPairingResult">
            <div class="small">
                <span v-if="lastPairingResult.result === 'SUCCESS' && lastPairingResult.timedOut">
                    {{ $t('Last paired device') }}<br>
                    <span v-if="lastPairingResult.name">{{ lastPairingResult.name }} - </span>
                    {{ formatDateTime(lastPairingResult.time) }}
                </span>
                <span v-else-if="!lastPairingResult.timedOut">
                    {{ $t(`subdevicePairingResult_${lastPairingResult.result}`, {resultNum: lastPairingResult.resultNum}) }}
                    <span v-if="lastPairingResult.name">({{ lastPairingResult.name }})</span>
                </span>
            </div>
        </div>
        <FormButton button-class="btn-default btn-wrapped" :disabled-reason="disabledReason" :loading="loading" @click="pairSubdevices()">
            <template>
                <div class="d-flex">
                    <div class="mr-2">
                        <fa :icon="faPlusCircle"/>
                    </div>
                    <div class="flex-grow-1">
                        {{ $t('Pair new devices or sensors') }}
                    </div>
                </div>
            </template>
            <template #loading>
                <div class="d-flex">
                    <span>{{ $t('Pairing request sent.') }}</span>
                    <button-loading-dots/>
                </div>
            </template>
        </FormButton>

        <!-- i18n: ['subdevicePairingResult_NOT_SUPPORTED', 'subdevicePairingResult_UNAUTHORIZED', 'subdevicePairingResult_ONGOING'] -->
        <!-- i18n: ['subdevicePairingResult_CMD_RESULT_UNKNOWN', 'subdevicePairingResult_PROCEDURE_STARTED', 'subdevicePairingResult_NO_NEW_DEVICE_FOUND'] -->
        <!-- i18n: ['subdevicePairingResult_SUCCESS', 'subdevicePairingResult_RESOURCES_LIMIT_EXCEEDED', 'subdevicePairingResult_NOT_STARTED_BUSY'] -->
        <!-- i18n: ['subdevicePairingResult_PAIRING_RESULT_UNKNOWN', 'subdevicePairingResult_NOT_STARTED_NOT_READY'] -->
        <!-- i18n: ['subdevicePairingResult_REQUEST_SENT'] -->
    </div>
</template>

<script setup>
  import FormButton from "@/common/gui/FormButton.vue";
  import {faPlusCircle} from "@fortawesome/free-solid-svg-icons";
  import {computed, onMounted, ref} from "vue";
  import {devicesApi} from "@/api/devices-api";
  import {promiseTimeout, useTimeoutPoll} from "@vueuse/core";
  import {formatDateTime} from "@/common/filters-date.js";
  import ButtonLoadingDots from "@/common/gui/loaders/button-loading-dots.vue";

  const props = defineProps({device: Object});

    const loading = ref(true);
    const lastPairingResult = ref(null);

    async function fetchState() {
        const {pairingResult: pr} = await devicesApi.getOne(props.device.id, 'pairingResult', {skipErrorHandler: true});
        // const pr = {"time": "2024-07-02T12:34:56Z", "result": "SUCCESS", "name": "ZAMEL", timedOut: true};
        // const pr = {"time": "2024-07-02T12:34:56Z", "result": "PROCEDURE_STARTED", "name": "ZAMEL", timedOut: false};
        // const pr = {"time": "2024-07-02T12:34:56Z", "result": "NO_NEW_DEVICE_FOUND", timedOut: false};
        if (pr?.result.startsWith('CMD_RESULT_')) {
            pr.resultNum = pr.result.substring('CMD_RESULT_'.length);
            pr.result = 'CMD_RESULT_UNKNOWN';
        }
        if (pr?.result.startsWith('PAIRING_RESULT_')) {
            pr.resultNum = pr.result.substring('PAIRING_RESULT_'.length);
            pr.result = 'PAIRING_RESULT_UNKNOWN';
        }
        lastPairingResult.value = pr;
        if (!pr || !['PROCEDURE_STARTED', 'ONGOING'].includes(pr.result) || pr.timedOut) {
            loading.value = false;
            pauseFetchingState();
        } else {
            resumeFetchingState();
        }
    }

    onMounted(async () => {
        await fetchState();
        if (lastPairingResult.value?.timedOut && lastPairingResult.value?.result !== 'SUCCESS') {
            lastPairingResult.value = null;
        }
    });

    const {resume: resumeFetchingState, pause: pauseFetchingState} = useTimeoutPoll(fetchState, 3000, {immediate: false});

    const disabledReason = computed(() => props.device.connected ? '' : 'Device is disconnected.');

    async function pairSubdevices() {
        loading.value = true;
        lastPairingResult.value = null;
        await devicesApi.pairSubdevice(props.device.id)
            .then(() => promiseTimeout(3000))
            .then(resumeFetchingState)
            .catch(() => loading.value = false);
    }
</script>

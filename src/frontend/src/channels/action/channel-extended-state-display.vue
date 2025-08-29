<template>
    <div v-if="source.state.extendedState" :class="{disconnected: !source.connected, condensed}">
        <div class="alert alert-warning small py-1 px-5 mb-2" v-if="!source.connected">
            {{ $t('The device is not connected. The values below represent the last known state.') }}
        </div>
        <div v-for="state in availableStates" :key="state.key">
            <dl class="m-0" v-if="source.state.extendedState[state.key] !== undefined">
                <dt>{{ $t(state.label) }}</dt>
                <dd>{{ state.format(source.state.extendedState[state.key]) }}</dd>
            </dl>
        </div>
    </div>
</template>

<script setup>
    import {computed} from "vue";
    import {useI18n} from "vue-i18n-bridge";
    import {Duration} from "luxon";
    import {useChannelsStore} from "@/stores/channels-store";
    import {useSuplaApi} from "@/api/use-supla-api";
    import {useTimeoutPoll} from "@vueuse/core/index";

    const props = defineProps({channel: Object, device: Object, condensed: Boolean});

    const channelsStore = useChannelsStore();

    const i18n = useI18n();

    const format = (v) => v;
    const percent = (v) => `${v}%`;
    const duration = (v) => Duration.fromObject({seconds: v}).rescale().toHuman({listStyle: 'long'});
    const yesNo = (v) => v ? i18n.t('Yes') : i18n.t('No');

    const source = computed(() => props.channel || channelsStore.list.find((c) => c.iodeviceId === props.device.id));

    // i18n: ['lastConnectionResetCause_UNKNOWN', 'lastConnectionResetCause_ACTIVITY_TIMEOUT', 'lastConnectionResetCause_WIFI_CONNECTION_LOST', 'lastConnectionResetCause_SERVER_CONNECTION_LOST']

    const availableStates = computed(() => {
        if (props.device) {
            return [
                {label: 'IP', key: 'ipv4', format}, // i18n
                {label: 'MAC', key: 'mac', format}, // i18n
                {label: 'Wi-Fi RSSI', key: 'wifiRSSI', format: (v) => `${v} dBm`}, // i18n
                {label: 'Wi-Fi signal strength', key: 'wifiSignalStrength', format: percent}, // i18n
                {label: 'Uptime', key: 'uptime', format: duration}, // i18n
                {label: 'Connection uptime', key: 'connectionUptime', format: duration}, // i18n
                {
                    label: 'Last connection reset cause', // i18n
                    key: 'lastConnectionResetCause',
                    format: (v) => i18n.t(`lastConnectionResetCause_${v}`)
                },
            ];
        } else {
            return [
                {label: 'Battery powered', key: 'batteryPowered', format: yesNo}, // i18n
                {label: 'Battery level', key: 'batteryLevel', format: percent}, // i18n
                {label: 'Battery health', key: 'batteryHealth', format: percent}, // i18n
                {label: 'Bridge node online', key: 'bridgeNodeOnline', format: yesNo}, // i18n
                {label: 'Bridge node signal strength', key: 'bridgeNodeSignalStrength', format: percent}, // i18n
                {label: 'Operating time', key: 'operatingTime', format: duration}, // i18n
                {label: 'Light source lifespan', key: 'lightSourceLifespan', format: percent}, // i18n
                {label: 'Light source operating time', key: 'lightSourceOperatingTime', format: duration}, // i18n
            ]
        }
    });

    const {execute: updateExtendedState} = useSuplaApi(`channels/${source.value.id}/settings`, {immediate: false}).patch({action: 'refreshState'});
    useTimeoutPoll(updateExtendedState, 10000, {immediate: true});
</script>
<style scoped>
    .disconnected {
        border: 1px solid #f60;
        padding: 15px;
        opacity: 0.8;
    }

    .condensed {
        dt, dd {
            display: inline-block;
            margin-right: 10px;
        }
    }
</style>

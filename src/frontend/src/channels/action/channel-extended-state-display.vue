<template>
    <div v-if="source && source.state.extendedState && sourceStates.length > 0" :class="{disconnected: !isConnected && !condensed, condensed}">
        <div class="alert alert-warning small py-1 px-5 mb-2" v-if="!isConnected">
            {{ $t('The device is not connected. The values below represent the last known state.') }}
        </div>
        <div v-for="state in sourceStates" :key="state.key">
            <dl class="m-0">
                <dt>{{ $t(state.label) }}</dt>
                <dd>{{ state.format(source.state.extendedState[state.key]) }}</dd>
            </dl>
        </div>
    </div>
</template>

<script setup>
  import {computed} from "vue";
  import {useI18n} from "vue-i18n";
  import {Duration} from "luxon";
  import {useChannelsStore} from "@/stores/channels-store";
  import {useSuplaApi} from "@/api/use-supla-api";
  import {useTimeoutPoll} from "@vueuse/core";
  import {useDevicesStore} from "@/stores/devices-store";

  const props = defineProps({channel: Object, device: Object, condensed: Boolean});

    const channelsStore = useChannelsStore();
    const devicesStore = useDevicesStore();

    const deviceId = computed(() => props.device ? props.device.id : props.channel.iodeviceId);
    const isConnected = computed(() => devicesStore.all[deviceId.value].connected);

    const i18n = useI18n();

    const format = (v) => v;
    const percent = (v) => `${v}%`;
    const duration = (v) => Duration.fromObject({seconds: v}).rescale().toHuman({maxUnits: 2});
    const yesNo = (v) => v ? i18n.t('Yes') : i18n.t('No');

    const source = computed(() => props.channel || channelsStore.list.find((c) => c.iodeviceId === props.device.id));

    // i18n: ['lastConnectionResetCause_UNKNOWN', 'lastConnectionResetCause_ACTIVITY_TIMEOUT', 'lastConnectionResetCause_WIFI_CONNECTION_LOST', 'lastConnectionResetCause_SERVER_CONNECTION_LOST']

    const availableStates = computed(() => {
        const states = [];
        if (props.device) {
            states.push(...[
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
            ]);
        } else {
            states.push(...[
                {label: 'Bridge node online', key: 'bridgeNodeOnline', format: yesNo}, // i18n
                {label: 'Bridge node signal strength', key: 'bridgeNodeSignalStrength', format: percent}, // i18n
                {label: 'Operating time', key: 'operatingTime', format: duration}, // i18n
                {label: 'Light source lifespan', key: 'lightSourceLifespan', format: (v) => `${v} ${i18n.t('hours')}`}, // i18n
                {label: 'Light source operating time', key: 'lightSourceOperatingTime', format: duration}, // i18n
                {label: 'Switch cycle count', key: 'switchCycleCount', format}, // i18n
            ]);
        }
        if ((source.value.state.extendedState?.batteryForDevice && props.device) || (!source.value.state.extendedState?.batteryForDevice && props.channel)) {
            states.push(...[
                {label: 'Power supply', key: 'batteryPowered', format: (v) => v ? i18n.t('battery') : i18n.t('mains')}, // i18n
                {label: 'Battery level', key: 'batteryLevel', format: percent}, // i18n
                {label: 'Battery health', key: 'batteryHealth', format: percent}, // i18n
            ]);
        }
        return states;
    });

    const sourceStates = computed(() => availableStates.value.filter((state) => source.value.state.extendedState[state.key] !== undefined));

    const {execute: updateExtendedState} = useSuplaApi(`channels/${source.value?.id}/settings`, {immediate: false}).patch({action: 'refreshState'});
    useTimeoutPoll(() => source.value && updateExtendedState(), 10000, {immediate: true});
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

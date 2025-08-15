<template>
    <div>
        <FormButton :button-class="['btn-xs', {'btn-default': !shown, 'btn-green mb-3': shown}]" @click="shown = !shown">
            <fa :icon="faInfoCircle"/>
            {{ $t('Device details') }}
        </FormButton>
        <TransitionExpand>
            <div v-if="shown">
                <dl>
                    <template v-for="state in availableStates">
                        <template v-if="channel.state.extendedState[state.key] !== undefined">
                            <dt :key="state.key">{{ $t(state.label) }}</dt>
                            <dd :key="state.key">{{ state.format(channel.state.extendedState[state.key]) }}</dd>
                        </template>
                    </template>
                </dl>
            </div>
        </TransitionExpand>
    </div>
</template>

<script setup>
    import {ref} from "vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {faInfoCircle} from "@fortawesome/free-solid-svg-icons";
    import FormButton from "@/common/gui/FormButton.vue";
    import {useI18n} from "vue-i18n-bridge";
    import {Duration} from "luxon";

    defineProps({channel: Object});

    const shown = ref(false);

    const i18n = useI18n();

    const format = (v) => v;
    const percent = (v) => `${v}%`;
    const duration = (v) => Duration.fromObject({seconds: v}).rescale().toHuman({listStyle: 'long'});

    // i18n: ['lastConnectionResetCause_0', 'lastConnectionResetCause_1', 'lastConnectionResetCause_2', 'lastConnectionResetCause_3']

    const availableStates = [
        {label: 'IP', key: 'ipv4', format}, // i18n
        {label: 'MAC', key: 'mac', format}, // i18n
        {label: 'Battery level', key: 'batteryLevel', format: percent}, // i18n
        {label: 'WiFi RSSI', key: 'wifiRSSI', format}, // i18n
        {label: 'WiFi signal strength', key: 'wifiSignalStrength', format: percent}, // i18n
        {label: 'Bridge node online', key: 'bridgeNodeOnline', format: (v) => v ? i18n.t('Yes') : i18n.t('No')}, // i18n
        {label: 'Bridge node signal strength', key: 'bridgeNodeSignalStrength', format: percent}, // i18n
        {label: 'Uptime', key: 'uptime', format: duration}, // i18n
        {label: 'Connection uptime', key: 'connectionUptime', format: duration}, // i18n
        {label: 'Battery health', key: 'batteryHealth', format: percent}, // i18n
        {label: 'Light source lifespan', key: 'lightSourceLifespan', format: percent}, // i18n
        {label: 'Last connection reset cause', key: 'lastConnectionResetCause', format: (v) => i18n.t(`lastConnectionResetCause_${v}`)}, // i18n
    ];
</script>

<style lang="scss" scoped>
    dl {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.5rem 1rem;
        margin: 0.5rem 0;
        align-items: center;

        dt {
            text-align: right;
        }
        dd {
            text-align: left;
        }
    }
</style>

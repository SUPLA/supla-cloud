<template>
    <div>
        <ChannelParamsElectricityMeterEnabledPhases :channel="channel" @change="$emit('change')"/>
        <dl>
            <dd>{{ $t('Associated measured channel') }}</dd>
            <dt>
                <channels-id-dropdown params="function=POWERSWITCH,LIGHTSWITCH,STAIRCASETIMER"
                    v-model="channel.config.relatedRelayChannelId"
                    @input="$emit('change')"/>
            </dt>
        </dl>
        <a class="d-flex accordion-header" @click="displayGroup('costs')">
            <span class="flex-grow-1">{{ $t('Costs') }}</span>
            <span>
                <fa :icon="group === 'costs' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'costs'">
                <channel-params-meter-cost :channel="channel" unit="kWh" @change="$emit('change')"/>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('history')">
            <span class="flex-grow-1">{{ $t('History') }}</span>
            <span>
                <fa :icon="group === 'history' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'history'">
                <ChannelParamsMeterKeepHistoryMode v-model="channel.config.voltageLoggerEnabled" @input="$emit('change')"
                    label-i18n="Store voltage history"/>
                <ChannelParamsMeterKeepHistoryMode v-model="channel.config.currentLoggerEnabled" @input="$emit('change')"
                    label-i18n="Store current history"/>
                <ChannelParamsMeterKeepHistoryMode v-model="channel.config.powerActiveLoggerEnabled" @input="$emit('change')"
                    label-i18n="Store active power history"/>
                <channel-params-electricity-meter-initial-values :channel="channel" @save="$emit('save')"/>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('voltageMonitoring')">
            <span class="flex-grow-1">{{ $t('Voltage monitoring') }}</span>
            <span>
                <fa :icon="group === 'voltageMonitoring' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'voltageMonitoring'">
                <ChannelParamsElectricityMeterVoltageThresholds :channel="channel" @change="$emit('change')"/>
            </div>
        </transition-expand>
        <div v-if="displayOthersTab">
            <a class="d-flex accordion-header" @click="displayGroup('other')">
                <span class="flex-grow-1">{{ $t('Other') }}</span>
                <span>
                    <fa :icon="group === 'other' ? 'chevron-down' : 'chevron-right'"/>
                </span>
            </a>
            <transition-expand>
                <div v-show="group === 'other'">
                    <ChannelParamsElectricityMeterOtherSettings :channel="channel" @change="$emit('change')"/>
                </div>
            </transition-expand>
        </div>
        <channel-params-meter-reset :channel="channel" class="mt-4"/>
    </div>
</template>

<script setup>
    import ChannelParamsMeterCost from "./channel-params-meter-cost";
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import ChannelParamsMeterReset from "@/channels/params/channel-params-meter-reset";
    import ChannelParamsElectricityMeterInitialValues from "@/channels/params/channel-params-electricity-meter-initial-values";
    import ChannelParamsElectricityMeterVoltageThresholds from "@/channels/params/channel-params-electricity-meter-voltage-thresholds";
    import ChannelParamsElectricityMeterEnabledPhases from "@/channels/params/channel-params-electricity-meter-enabled-phases";
    import ChannelParamsMeterKeepHistoryMode from "@/channels/params/channel-params-meter-keep-history-mode.vue";
    import {computed, ref} from "vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import ChannelParamsElectricityMeterOtherSettings from "@/channels/params/channel-params-electricity-meter-other-settings.vue";

    const props = defineProps({channel: Object});

    const group = ref(undefined);

    const displayOthersTab = computed(() => {
        const hasPhaseLedTypes = props.channel.config.availablePhaseLedTypes && props.channel.config.availablePhaseLedTypes.length > 0;
        const hasCTTypes = props.channel.config.availableCTTypes && props.channel.config.availableCTTypes.length > 0;
        return hasPhaseLedTypes || hasCTTypes;
    })

    function displayGroup(groupName) {
        if (group.value === groupName) {
            group.value = undefined;
        } else {
            group.value = groupName;
        }
    }
</script>

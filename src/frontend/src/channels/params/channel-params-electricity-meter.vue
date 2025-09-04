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
        <AccordionRoot>
            <AccordionItem title-i18n="Costs">
                <channel-params-meter-cost :channel="channel" unit="kWh" @change="$emit('change')"/>
            </AccordionItem>
            <AccordionItem title-i18n="History">
                <ChannelParamsMeterKeepHistoryMode v-model="channel.config.voltageLoggerEnabled" @input="$emit('change')"
                    label-i18n="Store voltage history"/>
                <ChannelParamsMeterKeepHistoryMode v-model="channel.config.currentLoggerEnabled" @input="$emit('change')"
                    label-i18n="Store current history"/>
                <ChannelParamsMeterKeepHistoryMode v-model="channel.config.powerActiveLoggerEnabled" @input="$emit('change')"
                    label-i18n="Store active power history"/>
                <channel-params-electricity-meter-initial-values :channel="channel" @save="$emit('save')"/>
            </AccordionItem>
            <AccordionItem title-i18n="Voltage monitoring">
                <ChannelParamsElectricityMeterVoltageThresholds :channel="channel" @change="$emit('change')"/>
            </AccordionItem>
            <AccordionItem title-i18n="Other" v-if="displayOthersTab">
                <ChannelParamsElectricityMeterOtherSettings :channel="channel" @change="$emit('change')"/>
            </AccordionItem>
        </AccordionRoot>

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
    import {computed} from "vue";
    import ChannelParamsElectricityMeterOtherSettings from "@/channels/params/channel-params-electricity-meter-other-settings.vue";
    import AccordionRoot from "@/common/gui/accordion/accordion-root.vue";
    import AccordionItem from "@/common/gui/accordion/accordion-item.vue";

    const props = defineProps({channel: Object});

    const displayOthersTab = computed(() => {
        const hasPhaseLedTypes = props.channel.config.availablePhaseLedTypes && props.channel.config.availablePhaseLedTypes.length > 0;
        const hasCTTypes = props.channel.config.availableCTTypes && props.channel.config.availableCTTypes.length > 0;
        return hasPhaseLedTypes || hasCTTypes;
    })
</script>

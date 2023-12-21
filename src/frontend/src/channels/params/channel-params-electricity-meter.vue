<template>
    <div>
        <ChannelParamsElectricityMeterEnabledPhases :channel="channel" @change="$emit('change')"/>
        <channel-params-meter-cost :channel="channel" unit="kWh" @change="$emit('change')"/>
        <dl>
            <dd>{{ $t('Associated measured channel') }}</dd>
            <dt>
                <channels-id-dropdown params="function=POWERSWITCH,LIGHTSWITCH,STAIRCASETIMER"
                    v-model="channel.config.relatedChannelId"
                    @input="$emit('change')"/>
            </dt>
        </dl>
        <div class="form-group"></div>
        <channel-params-electricity-meter-initial-values :channel="channel" @save="$emit('save')"/>
        <ChannelParamsElectricityMeterVoltageThresholds :channel="channel" @change="$emit('change')"/>
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
    import {defineProps} from "vue";

    defineProps({channel: Object});
</script>

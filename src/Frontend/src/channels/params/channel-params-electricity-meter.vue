<template>
    <dl v-if="channel.type.name == 'ELECTRICITYMETER'">
        <channel-params-any-meter :channel="channel" :unit="unit"
            @change="$emit('change')"></channel-params-any-meter>
    </dl>
    <dl v-else>
        <channel-params-meter-unit v-model="unit"
            :channel="channel" default_unit="kWh"
            @change="$emit('change')"></channel-params-meter-unit>
        <channel-params-any-meter :channel="channel" :unit="unit"
            @change="$emit('change')"></channel-params-any-meter>
    </dl>
</template>

<script>
    import ChannelParamsAnyMeter from "./channel-params-any-meter";
    import ChannelParamsMeterUnit from "./channel-params-meter-unit";

    export default {
        components: {ChannelParamsAnyMeter, ChannelParamsMeterUnit,},
        props: ['channel'],
        data() {
            return {
                unit: this.channel.type.name == 'ELECTRICITYMETER' ? 'kWh' : undefined
            };
        }
    };
</script>

<template>
    <div>

        <dl v-if="channel.type.name == 'ELECTRICITYMETER'">
            <channel-params-any-meter :channel="channel"
                :unit="unit"
                @change="$emit('change')"></channel-params-any-meter>
        </dl>
        <dl v-else>
            <channel-params-meter-unit v-model="unit"
                :channel="channel"
                @change="$emit('change')"></channel-params-meter-unit>
            <channel-params-any-meter :channel="channel"
                :unit="unit"
                @change="$emit('change')"></channel-params-any-meter>
            <channel-params-meter-impulses :channel="channel"
                :unit="unit"
                @change="$emit('change')"></channel-params-meter-impulses>
            <channel-params-meter-initial-value :channel="channel"
                :unit="unit"
                @change="$emit('change')"></channel-params-meter-initial-value>
        </dl>
    </div>
</template>

<script>
    import ChannelParamsAnyMeter from "./channel-params-any-meter";
    import ChannelParamsMeterUnit from "./channel-params-meter-unit";
    import ChannelParamsMeterImpulses from "./channel-params-meter-impulses";
    import ChannelParamsMeterInitialValue from "./channel-params-meter-initial-value";

    export default {
        components: {
            ChannelParamsAnyMeter, ChannelParamsMeterUnit, ChannelParamsMeterImpulses, ChannelParamsMeterInitialValue
        },
        props: ['channel'],
        data() {
            return {
                unit: this.channel.type.name == 'ELECTRICITYMETER' ? 'kWh' : undefined
            };
        }
    };
</script>

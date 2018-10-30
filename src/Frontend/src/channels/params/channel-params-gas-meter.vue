<template>
    <dl>
        <channel-params-meter-unit v-model="unit"
            :channel="channel"></channel-params-meter-unit>
        <channel-params-any-meter :channel="channel" :unit="unit"
            @change="$emit('change')"></channel-params-any-meter>
        <channel-params-meter-impulses :channel="channel" :unit="unit"
            @change="$emit('change')"></channel-params-meter-impulses>
    </dl>
</template>

<script>
    import ChannelParamsAnyMeter from "./channel-params-any-meter";
    import ChannelParamsMeterUnit from "./channel-params-meter-unit";
    import ChannelParamsMeterImpulses from "./channel-params-meter-impulses";

    export default {
        components: {ChannelParamsAnyMeter, ChannelParamsMeterUnit, ChannelParamsMeterImpulses},
        props: ['channel'],
        data() {
            return {
                unit: undefined
            };
        }
    };
</script>

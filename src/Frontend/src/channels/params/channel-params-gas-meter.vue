<template>
    <dl>
        <channel-params-any-meter :channel="channel"
            @change="$emit('change')"></channel-params-any-meter>
        <channel-params-meter-impulses :channel="channel"
            @change="$emit('change')"></channel-params-meter-impulses>
    </dl>
</template>

<script>
    import ChannelParamsAnyMeter from "./channel-params-any-meter";
    import ChannelParamsMeterImpulses from "./channel-params-meter-impulses";

    export default {
        components: {ChannelParamsAnyMeter, ChannelParamsMeterImpulses},
        props: ['channel']
    };
</script>

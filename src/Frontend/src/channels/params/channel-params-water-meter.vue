<template>
    <dl>
        <channel-params-any-meter :channel="channel"
            :unit='aa'
            @change="$emit('change')"></channel-params-any-meter>
        <channel-params-meter-impulses :channel="channel"
            @change="$emit('change')"></channel-params-meter-impulses>
    </dl>
</template>

<script>
    import ChannelParamsAnyMeter from "./channel-params-any-meter";

    export default {
        components: {ChannelParamsAnyMeter},
        props: ['channel']
    };
</script>

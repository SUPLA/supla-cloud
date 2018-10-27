<template>
    <dl>
        <channel-params-any-meter :channel="channel" :unit="'kWh'"
            @change="$emit('change')"></channel-params-any-meter>
    </dl>
</template>

<script>
    import ChannelParamsAnyMeter from "./channel-params-any-meter";

    export default {
        components: {ChannelParamsAnyMeter},
        props: ['channel']
    };
</script>

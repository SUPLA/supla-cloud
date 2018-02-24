<template>
    <channel-params-sensor-any :channel="channel"
        @change="$emit('change')"></channel-params-sensor-any>
</template>

<script>
    import ChannelParamsSensorAny from "./channel-params-sensor-any";

    export default {
        components: {ChannelParamsSensorAny},
        props: ['channel'],
    };
</script>

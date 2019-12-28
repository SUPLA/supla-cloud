<template>
    <channel-params-impulsecounter :channel="channel"
        @change="$emit('change')"></channel-params-impulsecounter>
</template>

<script>
    import ChannelParamsImpulsecounter from "./channel-params-impulsecounter";

    export default {
        components: {ChannelParamsImpulsecounter},
        props: ['channel'],
    };
</script>

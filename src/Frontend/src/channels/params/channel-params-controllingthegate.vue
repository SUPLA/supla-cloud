<template>
    <channel-params-controlling-any-lock :channel="channel"
        @change="$emit('change')"
        :times="[500, 1000, 2000]"
        related-channel-function="OPENINGSENSOR_GATE"></channel-params-controlling-any-lock>
</template>

<script>
    import ChannelParamsControllingAnyLock from "./channel-params-controlling-any-lock";

    export default {
        components: {ChannelParamsControllingAnyLock},
        props: ['channel']
    };
</script>

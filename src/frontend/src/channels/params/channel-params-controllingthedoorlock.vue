<template>
    <channel-params-controlling-any-lock :channel="channel"
        @change="$emit('change')"
        :times="[500, 1000, 2000, 4000, 6000, 8000, 10000]"
        related-channel-function="OPENINGSENSOR_DOOR"></channel-params-controlling-any-lock>
</template>

<script>
    import ChannelParamsControllingAnyLock from "./channel-params-controlling-any-lock";

    export default {
        components: {ChannelParamsControllingAnyLock},
        props: ['channel']
    };
</script>

<template>
    <channel-params-controlling-any-lock :channel="channel"
        @change="$emit('change')"
        related-channel-function="OPENINGSENSOR_GARAGEDOOR"></channel-params-controlling-any-lock>
</template>

<script>
    import ChannelParamsControllingAnyLock from "./channel-params-controlling-any-lock";

    export default {
        components: {ChannelParamsControllingAnyLock},
        props: ['channel']
    };
</script>

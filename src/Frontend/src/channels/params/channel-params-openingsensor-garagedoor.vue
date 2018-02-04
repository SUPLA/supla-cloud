<template>
    <channel-params-openingsensor-any :channel="channel"
        @change="$emit('change')"
        related-channel-function="CONTROLLINGTHEGARAGEDOOR"></channel-params-openingsensor-any>
</template>

<script>
    import ChannelParamsOpeningsensorAny from "./channel-params-openingsensor-any";

    export default {
        components: {ChannelParamsOpeningsensorAny},
        props: ['channel']
    };
</script>

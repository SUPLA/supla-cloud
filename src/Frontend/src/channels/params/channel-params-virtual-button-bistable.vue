<template>
    <channel-params-virtual-button :channel="channel"></channel-params-virtual-button>
</template>

<script>
    import ChannelParamsVirtualButton from "./channel-params-virtual-button";

    export default {
        components: {ChannelParamsVirtualButton},
        props: ['channel'],
    };
</script>

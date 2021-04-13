<template>
    <channel-params-controllingtherollershutter
        :channel="channel"
        sensor-function="OPENINGSENSOR_ROOFWINDOW"
        @change="$emit('change')"></channel-params-controllingtherollershutter>
</template>

<script>
    import ChannelParamsControllingtherollershutter from "./channel-params-controllingtherollershutter";

    export default {
        components: {ChannelParamsControllingtherollershutter},
        props: ['channel'],
    };
</script>
s

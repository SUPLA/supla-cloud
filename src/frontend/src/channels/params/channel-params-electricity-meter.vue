<template>
    <channel-params-meter-cost :channel="channel"
        unit="kWh"
        @change="$emit('change')"></channel-params-meter-cost>
</template>

<script>
    import ChannelParamsMeterCost from "./channel-params-meter-cost";

    export default {
        components: {ChannelParamsMeterCost},
        props: ['channel'],
    };
</script>

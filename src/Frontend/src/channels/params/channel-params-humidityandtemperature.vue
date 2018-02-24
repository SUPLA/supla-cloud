<template>
    <div>
        <channel-params-thermometer :channel="channel"
            @change="$emit('change')"></channel-params-thermometer>
        <channel-params-humidity :channel="channel"
            @change="$emit('change')"></channel-params-humidity>
    </div>
</template>

<script>
    import ChannelParamsThermometer from "./channel-params-thermometer";
    import ChannelParamsHumidity from "./channel-params-humidity";

    export default {
        components: {ChannelParamsHumidity, ChannelParamsThermometer},
        props: ['channel'],
    };
</script>

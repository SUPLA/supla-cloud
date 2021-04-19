<template>
    <channel-params-related-channel
        :channel="channel"
        label-i18n="Associated measurement channel"
        channel-filter="function=ELECTRICITYMETER,GASMETER,WATERMETER,HEATMETER"
        param-no="1"
        @change="$emit('change')"></channel-params-related-channel>
</template>

<script>
    import ChannelParamsRelatedChannel from "./channel-params-related-channel";

    export default {
        components: {ChannelParamsRelatedChannel},
        props: ['channel'],
    };
</script>

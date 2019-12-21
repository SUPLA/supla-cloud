<template>
    <dl v-if="channel.type.name == 'ELECTRICITYMETER'">
        <channel-params-meter-cost :channel="channel"
            :unit="unit"
            @change="$emit('change')"></channel-params-meter-cost>
    </dl>
    <dl v-else>
        <channel-params-impulsecounter :channel="channel"
            @change="$emit('change')"></channel-params-impulsecounter>
    </dl>
</template>

<script>
    import ChannelParamsImpulsecounter from "./channel-params-impulsecounter";

    export default {
        components: {ChannelParamsImpulsecounter},
        props: ['channel'],
    };
</script>

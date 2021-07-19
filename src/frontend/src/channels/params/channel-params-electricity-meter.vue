<template>
    <div>
        <channel-params-meter-cost :channel="channel"
            unit="kWh"
            @change="$emit('change')"></channel-params-meter-cost>
        <dl>
            <dd>{{ $t('Associated measured channel') }}</dd>
            <dt>
                <channels-id-dropdown params="function=POWERSWITCH,LIGHTSWITCH"
                    v-model="channel.config.relatedChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelParamsMeterCost from "./channel-params-meter-cost";
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";

    export default {
        components: {ChannelsIdDropdown, ChannelParamsMeterCost},
        props: ['channel'],
    };
</script>

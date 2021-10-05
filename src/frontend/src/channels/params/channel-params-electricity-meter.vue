<template>
    <div>
        <channel-params-meter-cost :channel="channel"
            unit="kWh"
            @change="$emit('change')"></channel-params-meter-cost>
        <dl>
            <dd>{{ $t('Associated measured channel') }}</dd>
            <dt>
                <channels-id-dropdown params="function=POWERSWITCH,LIGHTSWITCH,STAIRCASETIMER"
                    v-model="channel.config.relatedChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
        <div class="form-group"></div>
        <channel-params-meter-reset :channel="channel"></channel-params-meter-reset>
    </div>
</template>

<script>
    import ChannelParamsMeterCost from "./channel-params-meter-cost";
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import ChannelParamsMeterReset from "@/channels/params/channel-params-meter-reset";

    export default {
        components: {ChannelParamsMeterReset, ChannelsIdDropdown, ChannelParamsMeterCost},
        props: ['channel'],
    };
</script>

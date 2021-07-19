<template>
    <div>
        <channel-params-sensor-any :channel="channel"
            @change="$emit('change')"></channel-params-sensor-any>
        <dl>
            <dd>{{ $t('Channel for the sensor') }}</dd>
            <dt>
                <channels-id-dropdown :params="'function=' + relatedChannelFunction"
                    v-model="channel.config.openingSensorChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelParamsSensorAny from "./channel-params-sensor-any";
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";

    export default {
        components: {ChannelsIdDropdown, ChannelParamsSensorAny},
        props: ['channel', 'relatedChannelFunction'],
    };
</script>

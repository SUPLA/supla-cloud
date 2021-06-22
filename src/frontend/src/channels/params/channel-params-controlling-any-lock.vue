<template>
    <div>
        <channel-opening-time-selector v-if="channel.config.timeSettingAvailable"
            v-model="channel.config.relayTimeMs"
            @input="$emit('change')"
            :times="times"></channel-opening-time-selector>
        <dl>
            <dd>{{ $t('Opening sensor') }}</dd>
            <dt>
                <channels-id-dropdown :params="'function=' + relatedChannelFunction"
                    v-model="channel.config.openingSensorChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelOpeningTimeSelector from "./channel-opening-time-selector";
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";

    export default {
        components: {ChannelsIdDropdown, ChannelOpeningTimeSelector},
        props: ['channel', 'times', 'relatedChannelFunction'],
        data() {
            return {
                relatedChannel: undefined,
            };
        },
    };
</script>

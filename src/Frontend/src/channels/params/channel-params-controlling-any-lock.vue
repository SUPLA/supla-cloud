<template>
    <div>
        <channel-opening-time-selector v-model="channel.param1"
            @input="$emit('change')"
            :times="times"></channel-opening-time-selector>
        <channels-dropdown :params="'include=iodevice,location,function&function=' + relatedChannelFunction"
            v-model="relatedChannel"
            @input="channel.param2 = relatedChannel ? relatedChannel.id : 0 $emit('change')"></channels-dropdown>
    </div>
</template>

<script>
    import ChannelOpeningTimeSelector from "./channel-opening-time-selector";
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {
            ChannelsDropdown,
            ChannelOpeningTimeSelector
        },
        props: ['channel', 'times', 'relatedChannelFunction'],
        data() {
            return {
                relatedChannel: undefined,
            };
        }
    };
</script>

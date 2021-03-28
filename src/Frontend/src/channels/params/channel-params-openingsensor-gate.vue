<template>
    <div>
        <channel-params-openingsensor-any :channel="channel"
            @change="$emit('change')"
            related-channel-function="CONTROLLINGTHEGATE"></channel-params-openingsensor-any>
        <channel-params-related-channel
            :channel="channel"
            label-i18n="Channel for the partial opening sensor"
            channel-filter="function=CONTROLLINGTHEGATE"
            param-no="2"
            @change="$emit('change')"></channel-params-related-channel>
    </div>
</template>

<script>
    import ChannelParamsOpeningsensorAny from "./channel-params-openingsensor-any";
    import ChannelsDropdown from "../../devices/channels-dropdown";
    import ChannelParamsRelatedChannel from "@/channels/params/channel-params-related-channel";

    export default {
        components: {ChannelParamsRelatedChannel, ChannelsDropdown, ChannelParamsOpeningsensorAny},
        props: ['channel'],
        watch: {
            'channel.param1'() {
                if (this.channel.param1 == this.channel.param2) {
                    this.channel.param2 = 0;
                }
            },
            'channel.param2'() {
                if (this.channel.param1 == this.channel.param2) {
                    this.channel.param1 = 0;
                }
            }
        }
    };
</script>

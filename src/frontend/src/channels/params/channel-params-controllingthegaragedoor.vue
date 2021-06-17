<template>
    <div>
        <channel-params-controlling-any-lock :channel="channel"
            @change="$emit('change')"
            :times="[500, 1000, 2000]"
            related-channel-function="OPENINGSENSOR_GARAGEDOOR"></channel-params-controlling-any-lock>
        <channel-params-related-channel
            :channel="channel"
            label-i18n="Partial opening sensor"
            channel-filter="function=OPENINGSENSOR_GARAGEDOOR"
            param-no="3"
            @change="$emit('change')"></channel-params-related-channel>
    </div>
</template>

<script>
    import ChannelParamsControllingAnyLock from "./channel-params-controlling-any-lock";
    import ChannelParamsRelatedChannel from "@/channels/params/channel-params-related-channel";

    export default {
        components: {ChannelParamsRelatedChannel, ChannelParamsControllingAnyLock},
        props: ['channel'],
        watch: {
            'channel.param2'() {
                if (this.channel.param2 == this.channel.param3) {
                    this.channel.param3 = 0;
                }
            },
            'channel.param3'() {
                if (this.channel.param2 == this.channel.param3) {
                    this.channel.param2 = 0;
                }
            }
        }
    };
</script>

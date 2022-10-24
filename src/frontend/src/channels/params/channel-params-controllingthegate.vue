<template>
    <div>
        <channel-params-controlling-any-lock :channel="channel"
            @change="$emit('change')"
            :times="[500, 1000, 2000]"
            related-channel-function="OPENINGSENSOR_GATE"></channel-params-controlling-any-lock>
        <dl>
            <dd>{{ $t('Partial opening sensor') }}</dd>
            <dt>
                <channels-id-dropdown params="function=OPENINGSENSOR_GATE"
                    v-model="channel.config.openingSensorSecondaryChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
        <channel-params-controllingthegate-number-of-openclose-attempts :channel="channel" @change="$emit('change')"/>
        <ChannelParamsControllingthegateClosingRule :channel="channel" @change="$emit('change')"/>
    </div>
</template>

<script>
    import ChannelParamsControllingAnyLock from "./channel-params-controlling-any-lock";
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import ChannelParamsControllingthegateNumberOfOpencloseAttempts
        from "@/channels/params/channel-params-controllingthegate-number-of-openclose-attempts";
    import ChannelParamsControllingthegateClosingRule from "@/channels/params/channel-params-controllingthegate-closing-rule";

    export default {
        components: {
            ChannelParamsControllingthegateClosingRule,
            ChannelParamsControllingthegateNumberOfOpencloseAttempts, ChannelsIdDropdown, ChannelParamsControllingAnyLock
        },
        props: ['channel'],
        watch: {
            'channel.config.openingSensorChannelId'() {
                if (this.channel.config.openingSensorChannelId == this.channel.config.openingSensorSecondaryChannelId) {
                    this.channel.config.openingSensorSecondaryChannelId = 0;
                }
            },
            'channel.config.openingSensorSecondaryChannelId'() {
                if (this.channel.config.openingSensorChannelId == this.channel.config.openingSensorSecondaryChannelId) {
                    this.channel.config.openingSensorChannelId = 0;
                }
            }
        }
    };
</script>

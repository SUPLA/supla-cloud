<template>
    <div>
        <channel-params-openingsensor-any :channel="channel"
            @change="$emit('change')"
            related-channel-function="CONTROLLINGTHEGARAGEDOOR"></channel-params-openingsensor-any>
        <dl>
            <dd>{{ $t('Partially opened sensor for channel') }}</dd>
            <dt>
                <channels-id-dropdown params="function=CONTROLLINGTHEGARAGEDOOR"
                    v-model="channel.config.openingSensorSecondaryChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelParamsOpeningsensorAny from "./channel-params-openingsensor-any";
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";

    export default {
        components: {ChannelsIdDropdown, ChannelParamsOpeningsensorAny},
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

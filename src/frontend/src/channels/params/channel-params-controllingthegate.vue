<template>
    <div>
        <channel-params-controlling-any-lock :channel="channel"
            @change="$emit('change')"
            :times="[500, 1000, 2000]"
            related-channel-function="OPENINGSENSOR_GATE"></channel-params-controlling-any-lock>
        <dl>
            <dd>{{ $t('Partial opening sensor') }}</dd>
            <dt class="text-center"
                style="font-weight: normal">
                <channels-dropdown params="function=OPENINGSENSOR_GATE"
                    v-model="secondarySensor"
                    @input="secondarySensorChanged()"></channels-dropdown>
            </dt>
        </dl>
<!--        TODO -->
        <channel-params-related-channel
            :channel="channel"
            label-i18n="Partial opening sensor"
            channel-filter="function=OPENINGSENSOR_GATE"
            param-no="3"
            @change="$emit('change')"></channel-params-related-channel>
    </div>
</template>

<script>
    import ChannelParamsControllingAnyLock from "./channel-params-controlling-any-lock";
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {ChannelsDropdown, ChannelParamsControllingAnyLock},
        props: ['channel'],
        data() {
            return {
                secondarySensor: undefined,
            };
        },
        mounted() {
            this.updateSecondarySensor();
        },
        methods: {
            updateSecondarySensor() {
                if (this.channel.config.openingSensorSecondaryChannelId) {
                    this.$http.get(`channels/${this.channel.config.openingSensorSecondaryChannelId}`)
                        .then(response => this.secondarySensor = response.body);
                } else {
                    this.secondarySensor = undefined;
                }
            },
            secondarySensorChanged() {
                this.channel.config.openingSensorSecondaryChannelId = this.secondarySensor ? this.secondarySensor.id : 0;
                this.$emit('change');
            }
        },
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
                this.updateSecondarySensor();
            }
        }
    };
</script>

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
                if (this.channel.params.openingSensorSecondaryChannelId) {
                    this.$http.get(`channels/${this.channel.params.openingSensorSecondaryChannelId}`)
                        .then(response => this.secondarySensor = response.body);
                } else {
                    this.secondarySensor = undefined;
                }
            },
            secondarySensorChanged() {
                this.channel.params.openingSensorSecondaryChannelId = this.secondarySensor ? this.secondarySensor.id : 0;
                this.$emit('change');
            }
        },
        watch: {
            'channel.params.openingSensorChannelId'() {
                if (this.channel.params.openingSensorChannelId == this.channel.params.openingSensorSecondaryChannelId) {
                    this.channel.params.openingSensorSecondaryChannelId = 0;
                }
            },
            'channel.params.openingSensorSecondaryChannelId'() {
                if (this.channel.params.openingSensorChannelId == this.channel.params.openingSensorSecondaryChannelId) {
                    this.channel.params.openingSensorChannelId = 0;
                }
                this.updateSecondarySensor();
            }
        }
    };
</script>

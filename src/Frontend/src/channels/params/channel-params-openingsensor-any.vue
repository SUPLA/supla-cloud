<template>
    <div>
        <channel-params-sensor-any :channel="channel"
            @change="$emit('change')"></channel-params-sensor-any>
        <dl>
            <dd>{{ $t('Channel for the sensor') }}</dd>
            <dt class="text-center"
                style="font-weight: normal">
                <channels-dropdown :params="'function=' + relatedChannelFunction"
                    v-model="relatedChannel"
                    @input="relatedChannelChanged()"></channels-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelsDropdown from "../../devices/channels-dropdown";
    import ChannelParamsSensorAny from "./channel-params-sensor-any";

    export default {
        components: {ChannelParamsSensorAny, ChannelsDropdown},
        props: ['channel', 'relatedChannelFunction'],
        data() {
            return {
                relatedChannel: undefined,
            };
        },
        mounted() {
            this.updateRelatedChannel();
        },
        watch: {
            'channel.param1'() {
                this.updateRelatedChannel();
            }
        },
        methods: {
            updateRelatedChannel() {
                if (this.channel.param1) {
                    this.$http.get(`channels/${this.channel.param1}`).then(response => this.relatedChannel = response.body);
                } else {
                    this.relatedChannel = undefined;
                }
            },
            relatedChannelChanged() {
                this.channel.param1 = this.relatedChannel ? this.relatedChannel.id : 0;
                this.$emit('change');
            }
        }
    };
</script>

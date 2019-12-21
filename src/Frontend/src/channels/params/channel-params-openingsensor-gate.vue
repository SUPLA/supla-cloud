<template>
    <div>
        <channel-params-openingsensor-any :channel="channel"
            @change="$emit('change')"
            related-channel-function="CONTROLLINGTHEGATE"></channel-params-openingsensor-any>
        <dl>
            <dd>{{ $t('Channel for the partial opening sensor') }}</dd>
            <dt class="text-center"
                style="font-weight: normal">
                <channels-dropdown params="function=CONTROLLINGTHEGATE"
                    v-model="secondaryChannel"
                    @input="secondaryChannelChanged()"></channels-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelParamsOpeningsensorAny from "./channel-params-openingsensor-any";
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {ChannelsDropdown, ChannelParamsOpeningsensorAny},
        props: ['channel'],
        data() {
            return {
                secondaryChannel: undefined,
            };
        },
        mounted() {
            this.updateSecondaryChannel();
        },
        methods: {
            updateSecondaryChannel() {
                if (this.channel.params.controllingSecondaryChannelId) {
                    this.$http.get(`channels/${this.channel.params.controllingSecondaryChannelId}`)
                        .then(response => this.secondaryChannel = response.body);
                } else {
                    this.secondaryChannel = undefined;
                }
            },
            secondaryChannelChanged() {
                this.channel.params.controllingSecondaryChannelId = this.secondaryChannel ? this.secondaryChannel.id : 0;
                this.$emit('change');
            }
        },
        watch: {
            'channel.params.controllingChannelId'() {
                if (this.channel.params.controllingChannelId == this.channel.params.controllingSecondaryChannelId) {
                    this.channel.params.controllingSecondaryChannelId = 0;
                }
            },
            'channel.params.controllingSecondaryChannelId'() {
                if (this.channel.params.controllingChannelId == this.channel.params.controllingSecondaryChannelId) {
                    this.channel.params.controllingChannelId = 0;
                }
                this.updateSecondaryChannel();
            }
        }
    };
</script>

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
                if (this.channel.config.controllingSecondaryChannelId) {
                    this.$http.get(`channels/${this.channel.config.controllingSecondaryChannelId}`)
                        .then(response => this.secondaryChannel = response.body);
                } else {
                    this.secondaryChannel = undefined;
                }
            },
            secondaryChannelChanged() {
                this.channel.config.controllingSecondaryChannelId = this.secondaryChannel ? this.secondaryChannel.id : 0;
                this.$emit('change');
            }
        },
        watch: {
            'channel.config.controllingChannelId'() {
                if (this.channel.config.controllingChannelId == this.channel.config.controllingSecondaryChannelId) {
                    this.channel.config.controllingSecondaryChannelId = 0;
                }
            },
            'channel.config.controllingSecondaryChannelId'() {
                if (this.channel.config.controllingChannelId == this.channel.config.controllingSecondaryChannelId) {
                    this.channel.config.controllingChannelId = 0;
                }
                this.updateSecondaryChannel();
            }
        }
    };
</script>

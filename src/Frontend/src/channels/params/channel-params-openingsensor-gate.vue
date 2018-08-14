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
                if (this.channel.param2) {
                    this.$http.get(`channels/${this.channel.param2}`).then(response => this.secondaryChannel = response.body);
                } else {
                    this.secondaryChannel = undefined;
                }
            },
            secondaryChannelChanged() {
                this.channel.param2 = this.secondaryChannel ? this.secondaryChannel.id : 0;
                this.$emit('change');
            }
        },
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
                this.updateSecondaryChannel();
            }
        }
    };
</script>

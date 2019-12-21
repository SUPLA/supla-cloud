<template>
    <div>
        <channel-opening-time-selector v-model="channel.params.relayTimeMs"
            @input="$emit('change')"
            :times="times"></channel-opening-time-selector>
        <dl>
            <dd>{{ $t('Opening sensor') }}</dd>
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
    import ChannelOpeningTimeSelector from "./channel-opening-time-selector";
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {
            ChannelsDropdown,
            ChannelOpeningTimeSelector
        },
        props: ['channel', 'times', 'relatedChannelFunction'],
        data() {
            return {
                relatedChannel: undefined,
            };
        },
        mounted() {
            this.updateRelatedChannel();
        },
        watch: {
            'channel.params.openingSensorChannelId'() {
                this.updateRelatedChannel();
            }
        },
        methods: {
            updateRelatedChannel() {
                if (this.channel.params.openingSensorChannelId) {
                    this.$http.get(`channels/${this.channel.params.openingSensorChannelId}`)
                        .then(response => this.relatedChannel = response.body);
                } else {
                    this.relatedChannel = undefined;
                }
            },
            relatedChannelChanged() {
                this.channel.params.openingSensorChannelId = this.relatedChannel ? this.relatedChannel.id : 0;
                this.$emit('change');
            }
        }
    };
</script>

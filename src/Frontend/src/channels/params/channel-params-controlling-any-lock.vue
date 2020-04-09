<template>
    <div>
        <channel-opening-time-selector v-if="timeSelectorEnabled"
            v-model="channel.param1"
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
            'channel.param2'() {
                this.updateRelatedChannel();
            }
        },
        methods: {
            updateRelatedChannel() {
                if (this.channel.param2) {
                    this.$http.get(`channels/${this.channel.param2}`).then(response => this.relatedChannel = response.body);
                } else {
                    this.relatedChannel = undefined;
                }
            },
            relatedChannelChanged() {
                this.channel.param2 = this.relatedChannel ? this.relatedChannel.id : 0;
                this.$emit('change');
            }
        },
        computed: {
            timeSelectorEnabled() {
                return this.channel && !(this.channel.flags & 0x00100000);
            }
        }
    };
</script>

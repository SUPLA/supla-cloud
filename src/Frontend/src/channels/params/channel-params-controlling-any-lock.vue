<template>
    <div>
        <channel-opening-time-selector v-model="channel.param1"
            @input="$emit('change')"
            :times="channel.function.availableParams[1]"></channel-opening-time-selector>
        <dl>
            <dd>{{ $t('Open sensor') }}</dd>
            <dt>
                <channels-dropdown :params="'include=iodevice,location,function&function=' + relatedChannelFunction"
                    v-model="relatedChannel"
                    @input="channel.param2 = relatedChannel ? relatedChannel.id : 0 $emit('change')"></channels-dropdown>
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
        props: ['channel', 'relatedChannelFunction'],
        data() {
            return {
                relatedChannel: undefined,
            };
        },
        mounted() {
            if (this.channel.param2) {
                this.$http.get(`channels/${this.channel.param2}`).then(response => this.relatedChannel = response.body);
            }
        }
    };
</script>

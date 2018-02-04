<template>
    <div>
        <dl>
            <dd>{{ $t('Channel for the sensor') }}</dd>
            <dt class="text-center"
                style="font-weight: normal">
                <channels-dropdown :params="'include=iodevice,location,function&function=' + relatedChannelFunction"
                    v-model="relatedChannel"
                    @input="relatedChannelChanged()"></channels-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {ChannelsDropdown},
        props: ['channel', 'relatedChannelFunction'],
        data() {
            return {
                relatedChannel: undefined,
            };
        },
        mounted() {
            if (this.channel.param1) {
                this.$http.get(`channels/${this.channel.param1}`).then(response => this.relatedChannel = response.body);
            }
        },
        methods: {
            relatedChannelChanged() {
                this.channel.param1 = this.relatedChannel ? this.relatedChannel.id : 0;
                this.$emit('change');
            }
        }
    };
</script>

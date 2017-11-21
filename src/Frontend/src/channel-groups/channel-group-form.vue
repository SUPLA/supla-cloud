<template>
    <div>
        <h2>{{ $t(channelGroup.id ? 'Channel group ID' + channelGroup.id : 'New channel group') }}</h2>
        <form @submit.prevent="saveChannelGroup()">
            <input type="text"
                v-model="channelGroup.caption">
            <channels-dropdown :params="'include=iodevice,location&io=output&hasFunction=1' + (channelGroup.function ? '&function=' + channelGroup.function.id : '')"
                v-model="newChannel"
                @input="addChannel()"
                :hidden-channels="channelGroup.channels"></channels-dropdown>
            {{ channelGroup.channels }}
            <button type="submit">Zapisz</button>
        </form>
    </div>
</template>

<script>
    import ChannelsDropdown from "src/devices/channels-dropdown.vue";

    export default {
        props: ['channelGroup'],
        components: {ChannelsDropdown},
        data() {
            return {
                newChannel: undefined,
            };
        },
        methods: {
            saveChannelGroup() {
                this.$http.post('channel-groups', this.channelGroup);
            },
            addChannel() {
                if (this.newChannel) {
                    if (!this.channelGroup.channels) {
                        this.$set(this.channelGroup, 'channels', []);
                    }
                    this.channelGroup.channels.push(this.newChannel);
                    if (this.channelGroup.channels.length == 1 && !this.channelGroup.function) {
                        this.channelGroup.function = this.newChannel.function;
                    }
                    this.newChannel = undefined;
                }
            }
        }
    };
</script>

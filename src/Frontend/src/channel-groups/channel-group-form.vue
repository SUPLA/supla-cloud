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
        <square-links-grid v-if="channelGroup.channels"
            :count="channelGroup.channels.length"
            class="square-links-height-240">
            <div v-for="channel in channelGroup.channels"
                :key="channel.id">
                <channel-tile :channel="channel"></channel-tile>
            </div>
        </square-links-grid>
    </div>
</template>

<script>
    import ChannelsDropdown from "src/devices/channels-dropdown.vue";
    import SquareLinksGrid from "src/common/square-links-grid.vue";
    import ChannelTile from "./channel-tile.vue";

    export default {
        props: ['channelGroup'],
        components: {ChannelsDropdown, ChannelTile, SquareLinksGrid},
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

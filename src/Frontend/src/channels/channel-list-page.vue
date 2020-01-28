<template>
    <div>
        <loading-cover :loading="!channels">
            <div class="container"
                v-show="channels && channels.length">
                <channel-filters @filter-function="filterFunction = $event"
                    @compare-function="compareFunction = $event"
                    @filter="filter()"></channel-filters>
            </div>
            <div v-if="channels && channels.length">
                <square-links-grid v-if="filteredChannels.length"
                    :count="filteredChannels.length"
                    class="square-links-height-250">
                    <div v-for="channel in filteredChannels"
                        :key="channel.id">
                        <channel-tile :model="channel"></channel-tile>
                    </div>
                </square-links-grid>
                <empty-list-placeholder v-else></empty-list-placeholder>
            </div>
            <empty-list-placeholder v-else-if="channels"></empty-list-placeholder>
        </loading-cover>
    </div>
</template>

<script>
    import ChannelFilters from "./channel-filters";
    import ChannelTile from "./channel-tile";
    import EventBus from "src/common/event-bus";

    export default {
        components: {ChannelTile, ChannelFilters},
        props: ['deviceId'],
        data() {
            return {
                channels: undefined,
                filteredChannels: undefined,
                filterFunction: () => true,
                compareFunction: () => -1,
                loadNewChannelsListener: undefined
            };
        },
        mounted() {
            this.loadNewChannelsListener = () => this.loadChannels();
            EventBus.$on('device-count-changed', this.loadNewChannelsListener);
            this.loadChannels();
        },
        methods: {
            filter() {
                this.filteredChannels = this.channels ? this.channels.filter(this.filterFunction) : this.channels;
                if (this.filteredChannels) {
                    this.filteredChannels = this.filteredChannels.sort(this.compareFunction);
                }
            },
            loadChannels() {
                this.$http.get(this.endpoint).then(({body}) => {
                    this.channels = body;
                    this.filter();
                });
            }
        },
        computed: {
            endpoint() {
                let endpoint = 'channels?include=iodevice,location,state';
                if (this.deviceId) {
                    endpoint = `iodevices/${this.deviceId}/${endpoint}`;
                }
                return endpoint;
            }
        },
        beforeDestroy() {
            EventBus.$off('device-count-changed', this.loadNewChannelsListener);
        }
    };
</script>

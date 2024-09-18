<template>
    <div>
        <loading-cover :loading="!channels">
            <div class="container"
                v-show="channels && channels.length">
                <channel-filters :has-device="!!this.deviceId"
                    @filter-function="filterFunction = $event"
                    @compare-function="compareFunction = $event"
                    @filter="filter()"></channel-filters>
            </div>
            <div v-if="channels && channels.length">
                <div v-if="filteredChannels.length">
                    <div v-for="channelsGroup in channelsBySubDevice" :key="channelsGroup[0].subDeviceId">
                        <div class="container" v-if="channelsGroup[0].subDeviceId > 0">
                            <SubdeviceDetails :channel="channelsGroup[0]"/>
                        </div>
                        <square-links-grid :count="channelsGroup.length">
                            <div v-for="channel in channelsGroup"
                                :key="channel.id">
                                <channel-tile :model="channel"></channel-tile>
                            </div>
                        </square-links-grid>
                    </div>
                </div>
                <empty-list-placeholder v-else></empty-list-placeholder>
            </div>
            <empty-list-placeholder v-else-if="channels"></empty-list-placeholder>
        </loading-cover>
    </div>
</template>

<script>
    import ChannelFilters from "../../channels/channel-filters";
    import ChannelTile from "../../channels/channel-tile";
    import EventBus from "../../common/event-bus";
    import {groupBy, toArray} from "lodash";
    import SubdeviceDetails from "@/devices/details/subdevice-details.vue";

    export default {
        components: {SubdeviceDetails, ChannelTile, ChannelFilters},
        props: {
            deviceId: Number,
        },
        data() {
            return {
                channels: undefined,
                filteredChannels: undefined,
                filterFunction: () => true,
                compareFunction: () => -1,
                loadNewChannelsListener: undefined,
                device: undefined,
            };
        },
        mounted() {
            this.loadNewChannelsListener = () => this.loadChannels();
            EventBus.$on('total-count-changed', this.loadNewChannelsListener);
            this.loadChannels();
            this.$http.get(`iodevices/${this.deviceId}`).then(({body}) => this.device = body);
        },
        methods: {
            filter() {
                this.filteredChannels = this.channels ? this.channels.filter(this.filterFunction) : this.channels;
                if (this.filteredChannels) {
                    this.filteredChannels = this.filteredChannels.filter((channel) => !channel.config.hideInChannelsList);
                    this.filteredChannels = this.filteredChannels.sort(this.compareFunction);
                }
            },
            loadChannels() {
                this.$http.get(`iodevices/${this.deviceId}/channels?include=iodevice,location,state,connected`).then(({body}) => {
                    this.channels = body;
                    this.filter();
                });
            },
        },
        computed: {
            channelsBySubDevice() {
                return toArray(groupBy(this.filteredChannels, 'subDeviceId'));
            }
        },
        beforeDestroy() {
            EventBus.$off('total-count-changed', this.loadNewChannelsListener);
        }
    };
</script>

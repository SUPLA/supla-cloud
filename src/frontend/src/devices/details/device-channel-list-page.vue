<template>
    <div>
        <loading-cover :loading="!deviceChannels">
            <div class="container"
                v-show="deviceChannels && deviceChannels.length">
                <channel-filters :has-device="true"
                    @filter-function="filterFunction = $event"
                    @compare-function="compareFunction = $event"></channel-filters>
            </div>
            <div v-if="deviceChannels && deviceChannels.length">
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
            <empty-list-placeholder v-else-if="deviceChannels"></empty-list-placeholder>
        </loading-cover>
    </div>
</template>

<script>
    import ChannelFilters from "../../channels/channel-filters";
    import ChannelTile from "../../channels/channel-tile";
    import {groupBy, toArray} from "lodash";
    import SubdeviceDetails from "@/devices/details/subdevice-details.vue";
    import {mapState, mapStores} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";
    import {useDevicesStore} from "@/stores/devices-store";
    import {useSubDevicesStore} from "@/stores/subdevices-store";

    export default {
        components: {SubdeviceDetails, ChannelTile, ChannelFilters},
        props: {
            deviceId: Number,
        },
        data() {
            return {
                filterFunction: () => true,
                compareFunction: () => -1,
            };
        },
        computed: {
            channelsBySubDevice() {
                let channelGroups = toArray(groupBy(this.filteredChannels, 'subDeviceId'));
                channelGroups.forEach((channelsGroup) => {
                    const channel = channelsGroup[0];
                    channelsGroup.caption = this.subDevicesStore.forChannel(channel)?.name;
                    if (!channelsGroup.caption) {
                        channelsGroup.caption = channel.subDeviceId ? this.$t('Subdevice #{id}', {id: channel.subDeviceId}) : '___';
                    }
                });
                return channelGroups.sort(this.compareFunction);
            },
            ...mapState(useDevicesStore, {allDevices: 'all'}),
            ...mapStores(useSubDevicesStore),
            ...mapState(useChannelsStore, {allChannels: 'list'}),
            device() {
                return this.allDevices[this.deviceId];
            },
            deviceChannels() {
                return this.allChannels.filter((ch) => ch.iodeviceId === this.deviceId);
            },
            filteredChannels() {
                const filteredChannels = this.deviceChannels
                    .filter(this.filterFunction)
                    .filter((channel) => !channel.config.hideInChannelsList);
                filteredChannels.sort(this.compareFunction);
                return filteredChannels;
            }
        },
    };
</script>

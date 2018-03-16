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

<script type="text/babel">
    import ChannelFilters from "./channel-filters";
    import ChannelTile from "./channel-tile";

    export default {
        components: {
            ChannelTile,
            ChannelFilters
        },
        props: ['deviceId'],
        data() {
            return {
                channels: undefined,
                filteredChannels: undefined,
                filterFunction: () => true,
                compareFunction: () => -1,
            };
        },
        mounted() {
            let endpoint = 'channels?include=iodevice,location';
            if (this.deviceId) {
                endpoint = `devices/${this.deviceId}/${endpoint}`;
            }
            this.$http.get(endpoint).then(({body}) => {
                this.channels = body;
                this.filter();
            });
        },
        methods: {
            filter() {
                this.filteredChannels = this.channels ? this.channels.filter(this.filterFunction) : this.channels;
                if (this.filteredChannels) {
                    this.filteredChannels = this.filteredChannels.sort(this.compareFunction);
                }
            },
        }
    };
</script>

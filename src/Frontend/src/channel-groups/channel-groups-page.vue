<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1>{{ $t('Channel groups') }}</h1>
                    <loading-cover :loading="!channelGroups">
                        <div v-if="channelGroups">
                            <div class="grid-filters"
                                v-if="channelGroups.length">
                                <btn-filters v-model="filters.enabled"
                                    :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
                                <input type="text"
                                    class="form-control"
                                    v-model="filters.search"
                                    :placeholder="$t('Search')">
                            </div>
                            <div class="form-group">
                                <square-links-carousel
                                    tile="channel-group-tile"
                                    :items="filteredChannelGroups"
                                    :selected="channelGroup"
                                    @select="channelGroupChanged"
                                    :new-item-tile="filteredChannelGroups.length == channelGroups.length ? 'Create new channel group' : ''"></square-links-carousel>
                            </div>
                        </div>
                    </loading-cover>
                </div>
            </div>
        </div>
        <channel-group-details v-if="channelGroup"
            :model="channelGroup"
            @delete="onGroupDeleted()"
            @add="onGroupAdded($event)"
            @update="onGroupUpdated($event)">
        </channel-group-details>
    </div>
</template>

<script>
    import BtnFilters from "src/common/btn-filters";
    import ChannelGroupDetails from "./channel-group-details";
    import latinize from "latinize";
    import Vue from "vue";
    import SquareLinksCarousel from "../common/tiles/square-links-carousel";
    import ChannelGroupTile from "./channel-group-tile";

    Vue.component('ChannelGroupTile', ChannelGroupTile);

    export default {
        components: {SquareLinksCarousel, BtnFilters, ChannelGroupDetails},
        data() {
            return {
                channelGroup: undefined,
                channelGroups: undefined,
                filters: {
                    enabled: undefined,
                    search: '',
                }
            };
        },
        computed: {
            filteredChannelGroups() {
                let groups = this.channelGroups;
                if (this.filters.enabled !== undefined) {
                    groups = groups.filter(group => group.enabled == this.filters.enabled);
                }
                if (this.filters.search) {
                    groups = groups.filter(group => group.searchString.indexOf(latinize(this.filters.search).toLowerCase()) >= 0);
                }
                return groups;
            },
        },
        mounted() {
            this.$http.get('channel-groups')
                .then(({body}) => this.channelGroups = body)
                .then(() => Vue.nextTick(() => this.calculateSearchStrings()));
        },
        methods: {
            channelGroupChanged(channelGroup) {
                this.channelGroup = channelGroup;
            },
            calculateSearchStrings() {
                for (let group of this.channelGroups) {
                    const searchString = [group.id, group.caption, group.channelIds.length].join(' ');
                    this.$set(group, 'searchString', latinize(searchString).toLowerCase());
                }
            },
            onGroupAdded(channelGroup) {
                this.channelGroups.push(channelGroup);
                this.channelGroup = channelGroup;
                this.calculateSearchStrings();
            },
            onGroupUpdated(channelGroup) {
                const cg = this.channelGroups.find(c => channelGroup.id == c.id);
                $.extend(cg, channelGroup);
                this.calculateSearchStrings();
            },
            onGroupDeleted() {
                this.channelGroups.splice(this.channelGroups.indexOf(this.channelGroup), 1);
                this.channelGroup = undefined;
                this.calculateSearchStrings();
            }
        }
    };
</script>

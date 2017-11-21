<template>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h1>{{ $t('Channel groups') }}</h1>
                <div v-if="channelGroups">
                    <div class="grid-filters">
                        <btn-filters v-model="filters.enabled"
                            :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
                        <input type="text"
                            class="form-control"
                            v-model="filters.search"
                            :placeholder="$t('Search')">
                    </div>
                    <channel-groups-carousel :channel-groups="filteredChannelGroups"
                        @select="channelGroupChanged"></channel-groups-carousel>
                    <channel-group-form v-if="channelGroup"
                        :channel-group="channelGroup"></channel-group-form>
                </div>
                <loader-dots v-else></loader-dots>
            </div>
        </div>
    </div>
</template>

<script>
    import BtnFilters from "src/common/btn-filters.vue";
    import ChannelGroupForm from "./channel-group-form.vue";
    import ChannelGroupsCarousel from "./channel-groups-carousel.vue";
    import LoaderDots from "../common/loader-dots.vue";
    import latinize from "latinize";
    import Vue from "vue";

    export default {
        components: {BtnFilters, ChannelGroupForm, ChannelGroupsCarousel, LoaderDots},
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
            this.$http.get('channel-groups?include=channels')
                .then(({body}) => this.channelGroups = body)
                .then(() => Vue.nextTick(() => this.calculateSearchStrings()));
        },
        methods: {
            channelGroupChanged(channelGroup) {
                this.channelGroup = channelGroup;
            },
            calculateSearchStrings() {
                for (let group of this.channelGroups) {
                    const searchString = [group.id, group.caption, group.channels.length].join(' ');
                    this.$set(group, 'searchString', latinize(searchString).toLowerCase());
                }
            },
        }
    };
</script>

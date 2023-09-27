<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            id="channelsFiltersSort"
            :default-sort="hasDevice && 'channelNumber'"
            @input="$emit('filter')"
            :filters="filters"></btn-filters>
        <btn-filters v-model="functionality"
            class="always-dropdown"
            @input="$emit('filter')"
            :filters="[
                {label: $t('All'), value: '*'},
                {label: $t('With function'), value: 'withFunction'},
                {label: $t('Electric'), value: '130,140,180,190,200,300,310,315'},
                {label: $t('Doors, Gates, Windows'), value: '10,20,30,50,60,70,90,100,115,125,230,800,810'},
                {label: $t('Roller shutters'), value: '110,120'},
                {label: $t('Liquid, Temp'), value: '40,42,45,80'},
                {label: $t('Sensors'), value: '50,60,70,80,100,120,210,220,230,240,250,260,270,280'},
                {label: $t('Meters'), value: '310,315,320,330,340,520'},
                {label: $t('HVAC'), value: '420,422,423,424,425,426'},
                {label: $t('Other'), value: '290,400,410,500,510,700'},
                {label: $t('No function'), value: '0,-1'}
            ]"></btn-filters>
        <input type="text"
            @input="$emit('filter')"
            class="form-control"
            v-model="search"
            :placeholder="$t('Search')">
    </div>
</template>

<script>
    import BtnFilters from "../common/btn-filters";
    import latinize from "latinize";
    import {DateTime} from "luxon";

    export default {
        props: {
            hasDevice: {
                type: Boolean,
                default: false,
            },
        },
        components: {BtnFilters},
        data() {
            return {
                functionality: '*',
                search: '',
                sort: 'caption',
            };
        },
        mounted() {
            this.$emit('filter-function', (device) => this.matches(device));
            this.$emit('compare-function', (a, b) => this.compare(a, b));
        },
        methods: {
            matches(channel) {
                if (this.functionality && this.functionality !== '*') {
                    if (this.functionality === 'withFunction') {
                        if (!channel.function.id) {
                            return false;
                        }
                    } else if (this.functionality.split(',').indexOf('' + channel.function.id) === -1) {
                        return false;
                    }
                }
                if (this.search) {
                    const searchString = latinize([channel.id, channel.caption, channel.iodevice.name, this.$t(channel.type.caption),
                        channel.location.id, channel.location.caption, this.$t(channel.function.caption)].join(' '))
                        .toLowerCase();
                    return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
                }
                return true;
            },
            compare(a, b) {
                if (this.sort === 'channelNumber') {
                    return a.channelNumber - b.channelNumber;
                } else if (this.sort === 'lastAccess') {
                    return DateTime.fromISO(b.iodevice.lastConnected).diff(DateTime.fromISO(a.iodevice.lastConnected)).milliseconds;
                } else if (this.sort === 'caption') {
                    return this.captionForSort(a) < this.captionForSort(b) ? -1 : 1;
                } else if (this.sort === 'regDate') {
                    return DateTime.fromISO(b.iodevice.regDate).diff(DateTime.fromISO(a.iodevice.regDate)).milliseconds;
                } else {
                    return this.captionForSort(a.location) < this.captionForSort(b.location) ? -1 : 1;
                }
            },
            captionForSort(channel) {
                return latinize(channel.caption || (channel.function && this.$t(channel.function.caption)) || '').toLowerCase().trim();
            },
        },
        computed: {
            filters() {
                return [
                    {label: this.$t('As in device'), value: 'channelNumber', visible: this.hasDevice},
                    {label: this.$t('A-Z'), value: 'caption'},
                    {label: this.$t('Registered'), value: 'regDate', visible: !this.hasDevice},
                    {label: this.$t('Last access'), value: 'lastAccess', visible: !this.hasDevice},
                    {label: this.$t('Location'), value: 'location'}
                ];
            }
        },
    };
</script>

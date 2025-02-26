<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            id="channelsFiltersSort"
            :default-sort="hasDevice && 'channelNumber'"
            @input="filter()"
            :filters="filters"></btn-filters>
        <btn-filters v-model="connected"
            id="channelsFilterConnection"
            @input="filter()"
            :filters="[{label: $t('All'), value: 'all'}, {label: $t('Connected'), value: 'connected'}, {label: $t('Disconnected'), value: 'disconnected'}, {label: $t('Not available'), value: 'notAvailable'}]"></btn-filters>
        <btn-filters v-model="functionality"
            class="always-dropdown"
            @input="filter()"
            :filters="[
                {label: $t('All'), value: '*'},
                {label: $t('With function'), value: 'withFunction'},
                {label: $t('Electric'), value: '130,140,180,190,200,300,310,315'},
                {label: $t('Doors, Gates, Windows'), value: '10,20,30,50,60,70,90,100,115,125,230,800,810,950'},
                {label: $t('Roller shutters'), value: '110,120,900,910,930,940'},
                {label: $t('Liquid, Temp'), value: '40,42,45,80,500,510,980,981,982'},
                {label: $t('Sensors'), value: '50,60,70,80,100,120,210,220,230,235,236,240,250,260,270,280,990,1000'},
                {label: $t('Meters'), value: '310,315,320,330,340,520,530'},
                {label: $t('HVAC'), value: '400,410,420,422,423,424,425,426,960,970'},
                {label: $t('Other'), value: '290,700,920'},
                {label: $t('No function'), value: '0,-1'}
            ]"></btn-filters>
        <input type="text"
            @input="filter()"
            class="form-control"
            v-model="search"
            :placeholder="$t('Search')">
    </div>
</template>

<script>
    import BtnFilters from "../common/btn-filters";
    import latinize from "latinize";
    import {DateTime} from "luxon";
    import {mapState} from "pinia";
    import {useLocationsStore} from "@/stores/locations-store";
    import {useDevicesStore} from "@/stores/devices-store";

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
                connected: undefined,
                functionality: '*',
                search: '',
                sort: 'caption',
            };
        },
        mounted() {
            this.filter();
        },
        methods: {
            filter() {
                this.$emit('filter-function', (device) => this.matches(device));
                this.$emit('compare-function', (a, b) => this.compare(a, b));
            },
            matches(channel) {
                const connectedFilters = {
                    all: true,
                    disconnected: !channel.connected,
                    connected: channel.connected,
                    notAvailable: channel.state?.connectedCode === 'CONNECTED_NOT_AVAILABLE',
                };
                if (!connectedFilters[this.connected]) {
                    return false;
                }
                if (this.functionality && this.functionality !== '*') {
                    if (this.functionality === 'withFunction') {
                        if (!channel.functionId) {
                            return false;
                        }
                    } else if (this.functionality.split(',').indexOf('' + channel.functionId) === -1) {
                        return false;
                    }
                }
                if (this.search) {
                    const location = this.locations[channel.locationId] || {};
                    const device = this.devices[channel.iodeviceId] || {};
                    const searchString = latinize([channel.id, channel.caption, device.name, this.$t(channel.type.caption),
                        location.id, location.caption, this.$t(channel.function.caption)].join(' '))
                        .toLowerCase();
                    return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
                }
                return true;
            },
            compare(a, b) {
                if (this.sort === 'channelNumber') {
                    return a.channelNumber - b.channelNumber;
                } else if (this.sort === 'lastAccess') {
                    const deviceA = this.devices[a.iodeviceId] || {};
                    const deviceB = this.devices[b.iodeviceId] || {};
                    return DateTime.fromISO(deviceB.lastConnected).diff(DateTime.fromISO(deviceA.lastConnected)).milliseconds;
                } else if (this.sort === 'caption') {
                    return this.captionForSort(a) < this.captionForSort(b) ? -1 : 1;
                } else if (this.sort === 'regDate') {
                    const deviceA = this.devices[a.iodeviceId] || {};
                    const deviceB = this.devices[b.iodeviceId] || {};
                    return DateTime.fromISO(deviceB.regDate).diff(DateTime.fromISO(deviceA.regDate)).milliseconds;
                } else {
                    const locationA = this.locations[a.locationId] || {};
                    const locationB = this.locations[b.locationId] || {};
                    return this.captionForSort(locationA) < this.captionForSort(locationB) ? -1 : 1;
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
            },
            ...mapState(useLocationsStore, {locations: 'all'}),
            ...mapState(useDevicesStore, {devices: 'all'}),
        },
    };
</script>

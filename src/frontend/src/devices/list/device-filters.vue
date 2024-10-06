<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            id="deviceFiltersSort"
            @input="filter()"
            :filters="[{label: 'A-Z', value: 'az'}, {label: $t('Last access'), value: 'lastAccess'}, {label: $t('Registered'), value: 'regDate'}, {label: $t('Location'), value: 'location'}]"></btn-filters>
        <btn-filters v-model="enabled"
            @input="filter()"
            :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
        <btn-filters v-model="connected"
            @input="filter()"
            :filters="[{label: $t('All'), value: undefined}, {label: $t('Connected'), value: true}, {label: $t('Disconnected'), value: false}]"></btn-filters>
        <input type="text"
            @input="filter()"
            class="form-control"
            v-model="search"
            :placeholder="$t('Search')">
    </div>
</template>

<script>
    import BtnFilters from "../../common/btn-filters";
    import latinize from "latinize";
    import {DateTime} from "luxon";
    import {mapState} from "pinia";
    import {useLocationsStore} from "@/stores/locations-store";

    export default {
        components: {BtnFilters},
        data() {
            return {
                sort: 'az',
                enabled: undefined,
                connected: undefined,
                search: '',
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
            matches(device) {
                if (this.enabled !== undefined && this.enabled != device.enabled) {
                    return false;
                }
                if (this.connected !== undefined && this.connected != device.connected) {
                    return false;
                }
                if (this.search) {
                    const location = this.locations[device.locationId];
                    const searchString = latinize([device.id, device.name, device.gUIDString, device.softwareVersion, device.comment,
                        location.id, location.caption].join(' ')).toLowerCase();
                    return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
                }
                return true;
            },
            compare(a, b) {
                if (this.sort === 'lastAccess') {
                    return DateTime.fromISO(b.lastConnected).diff(DateTime.fromISO(a.lastConnected)).milliseconds;
                } else if (this.sort === 'regDate') {
                    return DateTime.fromISO(b.regDate).diff(DateTime.fromISO(a.regDate)).milliseconds;
                } else if (this.sort === 'location') {
                    const locationA = this.locations[a.locationId] || {};
                    const locationB = this.locations[b.locationId] || {};
                    return this.captionForSort(locationA) < this.captionForSort(locationB) ? -1 : 1;
                } else {
                    return this.captionForSort(a) < this.captionForSort(b) ? -1 : 1;
                }
            },
            captionForSort(model) {
                return latinize(model.comment || model.caption || model.name || '').toLowerCase().trim();
            },
        },
        computed: {
            ...mapState(useLocationsStore, {locations: 'all'}),
        }
    };
</script>

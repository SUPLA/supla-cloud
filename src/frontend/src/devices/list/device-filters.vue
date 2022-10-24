<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            id="deviceFiltersSort"
            @input="$emit('filter')"
            :filters="[{label: 'A-Z', value: 'az'}, {label: $t('Last access'), value: 'lastAccess'}, {label: $t('Registered'), value: 'regDate'}, {label: $t('Location'), value: 'location'}]"></btn-filters>
        <btn-filters v-model="enabled"
            @input="$emit('filter')"
            :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
        <btn-filters v-model="connected"
            @input="$emit('filter')"
            :filters="[{label: $t('All'), value: undefined}, {label: $t('Connected'), value: true}, {label: $t('Disconnected'), value: false}]"></btn-filters>
        <input type="text"
            @input="$emit('filter')"
            class="form-control"
            v-model="search"
            :placeholder="$t('Search')">
    </div>
</template>

<script>
    import BtnFilters from "../../common/btn-filters";
    import latinize from "latinize";
    import {DateTime} from "luxon";

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
            this.$emit('filter-function', (device) => this.matches(device));
            this.$emit('compare-function', (a, b) => this.compare(a, b));
        },
        methods: {
            matches(device) {
                if (this.enabled !== undefined && this.enabled != device.enabled) {
                    return false;
                }
                if (this.connected !== undefined && this.connected != device.connected) {
                    return false;
                }
                if (this.search) {
                    const searchString = latinize([device.id, device.name, device.gUIDString, device.softwareVersion, device.comment,
                        device.location.id, device.location.caption].join(' ')).toLowerCase();
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
                    return this.captionForSort(a.location) < this.captionForSort(b.location) ? -1 : 1;
                } else {
                    return this.captionForSort(a) < this.captionForSort(b) ? -1 : 1;
                }
            },
            captionForSort(model) {
                return latinize(model.comment || model.caption || model.name).toLowerCase().trim();
            },
        }
    };
</script>

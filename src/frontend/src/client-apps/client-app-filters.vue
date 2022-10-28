<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            id="clientAppsSort"
            @input="$emit('filter')"
            :filters="[{label: 'A-Z', value: 'az'}, {label: $t('Last access'), value: 'lastAccess'}]"></btn-filters>
        <btn-filters v-model="enabled"
            @input="$emit('filter')"
            :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
        <btn-filters v-model="connected"
            @input="$emit('filter')"
            :filters="[{label: $t('All'), value: undefined}, {label: $t('Active'), value: true}, {label: $t('Idle'), value: false}]"></btn-filters>
        <input type="text"
            class="form-control"
            v-model="search"
            @input="$emit('filter')"
            :placeholder="$t('Search')">
    </div>
</template>

<script>
    import BtnFilters from "../common/btn-filters";
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
            this.$emit('filter-function', (clientApp) => this.matches(clientApp));
            this.$emit('compare-function', (a, b) => this.compare(a, b));
        },
        methods: {
            matches(clientApp) {
                if (this.enabled !== undefined && this.enabled != clientApp.enabled) {
                    return false;
                }
                if (this.connected !== undefined && this.connected != clientApp.connected) {
                    return false;
                }
                if (this.search) {
                    const parts = [
                        clientApp.id,
                        clientApp.caption,
                        clientApp.softwareVersion,
                        clientApp.protocolVersion,
                        clientApp.lastAccessIpv4,
                        clientApp.accessId ? clientApp.accessId.caption : '',
                    ];
                    const searchString = latinize(parts.join(' ')).toLowerCase();
                    return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
                }
                return true;
            },
            compare(a1, a2) {
                if (this.sort == 'az') {
                    return this.captionForSort(a1) < this.captionForSort(a2) ? -1 : 1;
                } else {
                    return DateTime.fromISO(a2.lastAccessDate).diff(DateTime.fromISO(a1.lastAccessDate)).milliseconds;
                }
            },
            captionForSort(model) {
                return latinize(model.caption || model.name).toLowerCase().trim();
            },
        }
    };
</script>

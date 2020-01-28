<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            @input="$emit('filter')"
            :filters="[{label: 'A-Z', value: 'az'}, {label: $t('Next run date'), value: 'nextRunDate'}]"></btn-filters>
        <btn-filters v-model="enabled"
            @input="$emit('filter')"
            :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
        <input type="text"
            class="form-control"
            v-model="search"
            @input="$emit('filter')"
            :placeholder="$t('Search')">
    </div>
</template>

<script>
    import BtnFilters from "src/common/btn-filters";
    import latinize from "latinize";

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
            matches(schedule) {
                if (this.enabled !== undefined && this.enabled != schedule.enabled) {
                    return false;
                }
                if (this.search) {
                    const parts = [
                        schedule.id,
                        schedule.caption,
                    ];
                    const searchString = latinize(parts.join(' ')).toLowerCase();
                    return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
                }
                return true;
            },
            compare(a1, a2) {
                if (this.sort == 'az') {
                    return latinize(a1.caption || '').toLowerCase() < latinize(a2.caption || '').toLowerCase() ? -1 : 1;
                } else {
                    const closestA1 = a1.closestExecutions.future[0];
                    const closestA2 = a2.closestExecutions.future[0];
                    if (!closestA2) {
                        return -1;
                    } else if (!closestA1) {
                        return 1;
                    } else {
                        return moment(closestA1.plannedTimestamp).diff(moment(closestA2.plannedTimestamp));
                    }
                }
            }
        }
    };
</script>

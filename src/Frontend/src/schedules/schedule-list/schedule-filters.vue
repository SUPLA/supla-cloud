<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            @input="$emit('filter')"
            :filters="[{label: $t('A-Z'), value: 'az'}, {label: $t('Next run date'), value: 'nextRunDate'}]"></btn-filters>
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
                    return a1.caption.toLowerCase() < a2.caption.toLowerCase() ? -1 : 1;
                } else {
                    // return moment(a2.lastAccessDate).diff(moment(a1.lastAccessDate));
                }
            }
        }
    };
</script>

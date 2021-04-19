<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            id="channelsFiltersSort"
            @input="$emit('filter')"
            :filters="[{label: $t('ID'), value: 'id'}, {label: $t('A-Z'), value: 'caption'}, {label: $t('Last used'), value: 'lastUsed'}]"></btn-filters>
        <btn-filters v-model="active"
            @input="$emit('filter')"
            :filters="[{label: $t('All'), value: undefined}, {label: $t('Active'), value: true}, {label: $t('Inactive'), value: false}]"></btn-filters>
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
    import moment from "moment";

    export default {
        components: {BtnFilters},
        data() {
            return {
                active: undefined,
                search: '',
                sort: 'id'
            };
        },
        mounted() {
            this.$emit('filter-function', (directLink) => this.matches(directLink));
            this.$emit('compare-function', (a, b) => this.compare(a, b));
        },
        methods: {
            matches(directLink) {
                if (this.active !== undefined) {
                    if (this.active !== directLink.active) {
                        return false;
                    }
                }
                if (this.search) {
                    const searchString = latinize([directLink.id, directLink.caption].join(' '))
                        .toLowerCase();
                    return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
                }
                return true;
            },
            compare(a, b) {
                if (this.sort === 'lastUsed') {
                    return moment(b.lastUsed || '2000-01-01T00:00:00').diff(moment(a.lastUsed || '2000-01-01T00:00:00'));
                } else if (this.sort === 'caption') {
                    return a.caption.toLowerCase() < b.caption.toLowerCase() ? -1 : 1;
                } else {
                    return b.id - a.id;
                }
            }
        }
    };
</script>

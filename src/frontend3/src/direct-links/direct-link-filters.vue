<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            id="directLinksSort"
            @input="$emit('filter')"
            :filters="[{label: $t('A-Z'), value: 'caption'}, {label: $t('ID'), value: 'id'}, {label: $t('Last used'), value: 'lastUsed'}]"></btn-filters>
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
    import {DateTime} from "luxon";

    export default {
        components: {BtnFilters},
        data() {
            return {
                active: undefined,
                search: '',
                sort: 'caption',
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
                    return DateTime.fromISO(b.lastUsed || '2000-01-01T00:00:00')
                        .diff(DateTime.fromISO(a.lastUsed || '2000-01-01T00:00:00')).milliseconds;
                } else if (this.sort === 'caption') {
                    return this.captionForSort(a) < this.captionForSort(b) ? -1 : 1;
                } else {
                    return +a.id - +b.id;
                }
            },
            captionForSort(model) {
                return latinize(model.caption).toLowerCase().trim();
            },
        }
    };
</script>

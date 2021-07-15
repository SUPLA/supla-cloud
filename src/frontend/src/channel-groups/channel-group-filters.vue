<template>
    <div class="grid-filters"
        v-if="items.length">
        <btn-filters v-model="hidden"
            @input="$emit('filter')"
            :filters="[{label: $t('All'), value: undefined}, {label: $t('Invisible'), value: true}, {label: $t('Visible'), value: false}]"></btn-filters>
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

    export default {
        components: {BtnFilters},
        props: ['items'],
        data() {
            return {
                hidden: undefined,
                search: '',
            };
        },
        mounted() {
            this.$emit('filter-function', (channelGroup) => this.matches(channelGroup));
        },
        methods: {
            matches(channelGroup) {
                if (this.hidden !== undefined && this.hidden != channelGroup.hidden) {
                    return false;
                }
                if (this.search) {
                    const searchString = latinize([channelGroup.id, channelGroup.caption].join(' ')).toLowerCase();
                    return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
                }
                return true;
            }
        }
    };
</script>

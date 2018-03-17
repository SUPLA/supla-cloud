<template>
    <div class="grid-filters">
        <btn-filters v-model="sort"
            id="channelsFiltersSort"
            @input="$emit('filter')"
            :filters="[{label: $t('Registered'), value: 'regDate'}, {label: $t('Last access'), value: 'lastAccess'}, {label: $t('Location'), value: 'location'}]"></btn-filters>
        <btn-filters v-model="functionality"
            @input="$emit('filter')"
            :filters="[
                {label: $t('All'), value: '*'},
                {label: $t('Electric'), value: '130,140,180,190,200'},
                {label: $t('Doors, Gates'), value: '10,20,30,50,60,70,90,100'},
                {label: $t('Roller shutters'), value: '110,120'},
                {label: $t('Liquid, Temp'), value: '40,42,45,80'},
                {label: $t('Sensors'), value: '50,60,70,80,100,120,210,220'},
                {label: $t('No function'), value: '0'}
            ]"></btn-filters>
        <input type="text"
            @input="$emit('filter')"
            class="form-control"
            v-model="search"
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
                functionality: '*',
                search: '',
                sort: 'regDate'
            };
        },
        mounted() {
            this.$emit('filter-function', (device) => this.matches(device));
            this.$emit('compare-function', (a, b) => this.compare(a, b));
        },
        methods: {
            matches(channel) {
                if (this.functionality && this.functionality !== '*' && this.functionality.split(',').indexOf('' + channel.function.id) === -1) {
                    return false;
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
                if (this.sort === 'lastAccess') {
                    return moment(b.iodevice.lastConnected).diff(moment(a.iodevice.lastConnected));
                } else if (this.sort === 'regDate') {
                    return moment(b.iodevice.regDate).diff(moment(a.iodevice.regDate));
                } else {
                    return a.location.caption.toLowerCase() < b.location.caption.toLowerCase() ? -1 : 1;
                }
            }
        }
    };
</script>

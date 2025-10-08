<template>
  <div v-if="items.length" class="grid-filters">
    <btn-filters
      id="locationsSort"
      v-model="sort"
      :filters="[
        {label: $t('A-Z'), value: 'caption'},
        {label: $t('ID'), value: 'id'},
        {label: $t('No of devices'), value: 'noOfDevices'},
      ]"
      @input="$emit('filter')"
    ></btn-filters>
    <btn-filters
      v-model="enabled"
      :filters="[
        {label: $t('All'), value: undefined},
        {label: $t('Enabled'), value: true},
        {label: $t('Disabled'), value: false},
      ]"
      @input="$emit('filter')"
    ></btn-filters>
    <input v-model="search" type="text" class="form-control" :placeholder="$t('Search')" @input="$emit('filter')" />
  </div>
</template>

<script>
  import BtnFilters from '../common/btn-filters.vue';
  import latinize from 'latinize';

  export default {
    components: {BtnFilters},
    props: ['items'],
    data() {
      return {
        enabled: undefined,
        search: '',
        sort: 'caption',
      };
    },
    mounted() {
      this.$emit('filter-function', (location) => this.matches(location));
      this.$emit('compare-function', (a, b) => this.compare(a, b));
    },
    methods: {
      matches(location) {
        if (this.enabled !== undefined && this.enabled != location.enabled) {
          return false;
        }
        if (this.search) {
          const searchString = latinize([location.id, location.caption].join(' ')).toLowerCase();
          return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
        }
        return true;
      },
      compare(a, b) {
        if (this.sort === 'noOfDevices') {
          return b.relationsCount.ioDevices - a.relationsCount.ioDevices;
        } else if (this.sort === 'caption') {
          return this.captionForSort(a) < this.captionForSort(b) ? -1 : 1;
        } else {
          return +a.id - +b.id;
        }
      },
      captionForSort(model) {
        return latinize(model.caption).toLowerCase().trim();
      },
    },
  };
</script>

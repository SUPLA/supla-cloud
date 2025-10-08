<template>
  <div v-if="items.length" class="grid-filters">
    <btn-filters
      id="channelGroupsSort"
      v-model="sort"
      :filters="[
        {label: $t('A-Z'), value: 'caption'},
        {label: $t('ID'), value: 'id'},
        {label: $t('No of channels'), value: 'noOfChannels'},
      ]"
      @input="$emit('filter')"
    ></btn-filters>
    <btn-filters
      v-model="hidden"
      :filters="[
        {label: $t('All'), value: undefined},
        {label: $t('Invisible'), value: true},
        {label: $t('Visible'), value: false},
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
        hidden: undefined,
        search: '',
        sort: 'caption',
      };
    },
    mounted() {
      this.$emit('filter-function', (channelGroup) => this.matches(channelGroup));
      this.$emit('compare-function', (a, b) => this.compare(a, b));
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
      },
      compare(a, b) {
        if (this.sort === 'noOfChannels') {
          return b.relationsCount.channels - a.relationsCount.channels;
        } else if (this.sort === 'caption') {
          return this.captionForSort(a) < this.captionForSort(b) ? -1 : 1;
        } else {
          return +a.id - +b.id;
        }
      },
      captionForSort(model) {
        return latinize(model.caption || '')
          .toLowerCase()
          .trim();
      },
    },
  };
</script>

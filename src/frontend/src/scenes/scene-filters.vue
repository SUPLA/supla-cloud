<template>
    <div class="grid-filters"
        v-if="items.length">
        <btn-filters v-model="sort"
          id="locationsSort"
          @input="$emit('filter')"
          :filters="[{label: $t('A-Z'), value: 'caption'}, {label: $t('ID'), value: 'id'}]"></btn-filters>
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
  import BtnFilters from "../common/btn-filters.vue";
  import latinize from "latinize";

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
            this.$emit('filter-function', (scene) => this.matches(scene));
            this.$emit('compare-function', (a, b) => this.compare(a, b));
        },
        methods: {
            matches(scene) {
                if (this.enabled !== undefined && this.enabled !== scene.enabled) {
                    return false;
                }
                if (this.search) {
                    const searchString = latinize([scene.id, scene.caption].join(' ')).toLowerCase();
                    return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
                }
                return true;
            },
            compare(a, b) {
              if (this.sort === 'caption') {
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

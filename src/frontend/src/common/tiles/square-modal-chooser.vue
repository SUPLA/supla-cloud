<template>
  <modal
    class="square-modal-chooser"
    :header="$t(titleI18n)"
    :cancellable="true"
    @cancel="$emit('cancel')"
    @confirm="$emit('confirm', multiple ? selectedItems : selectedItems[0])"
  >
    <loading-cover :loading="!items">
      <component
        :is="filters"
        v-if="filters && items"
        :items="items"
        @filter-function="
          filterFunction = $event;
          filter();
        "
        @compare-function="
          compareFunction = $event;
          filter();
        "
        @filter="filter()"
      ></component>
      <square-links-grid :count="5">
        <div v-for="item in filteredItems" :key="item.id">
          <component :is="tile" :class="isSelected(item) ? 'selected' : ''" :no-link="true" :model="item" @click="onItemClick(item)" />
        </div>
      </square-links-grid>
    </loading-cover>
  </modal>
</template>

<script>
  import {api} from '@/api/api.js';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import Modal from '@/common/modal.vue';
  import {debounce} from 'lodash';
  import SquareLinksGrid from '@/common/tiles/square-links-grid.vue';

  export default {
    compatConfig: {WATCH_ARRAY: false},
    components: {SquareLinksGrid, Modal, LoadingCover},
    props: ['selected', 'titleI18n', 'tile', 'filters', 'endpoint'],
    data() {
      return {
        items: undefined,
        selectedIds: [],
        filteredItems: undefined,
        filterFunction: () => true,
        compareFunction: () => -1,
        multiple: false,
      };
    },
    mounted() {
      api.get(this.endpoint).then((response) => (this.items = response.body));
      if (this.selected) {
        this.multiple = Array.isArray(this.selected);
        this.selectedIds = this.multiple ? this.selected.map((item) => item.id || item) : [this.selected.id || this.selected];
      }
    },
    methods: {
      filter: debounce(function () {
        this.filteredItems = this.items ? this.items.filter(this.filterFunction) : this.items;
        if (this.filteredItems) {
          this.filteredItems = this.filteredItems.sort(this.compareFunction);
        }
      }, 50),
      onItemClick(item) {
        if (this.isSelected(item)) {
          this.selectedIds.splice(this.selectedIds.indexOf(item.id), 1);
        } else {
          this.selectedIds.push(item.id);
        }
        if (!this.multiple) {
          this.selectedIds = this.isSelected(item) ? [item.id] : [];
        }
      },
      isSelected(item) {
        return this.selectedIds.includes(item.id);
      },
    },
    computed: {
      selectedItems() {
        return this.items.filter((item) => this.isSelected(item));
      },
    },
    watch: {
      selected() {
        this.selectedItems = this.selected;
      },
    },
  };
</script>

<style lang="scss">
  @use '../../styles/variables' as *;

  .square-modal-chooser {
    .modal-container {
      max-width: initial;
    }
  }
</style>

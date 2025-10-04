<template>
    <modal class="square-modal-chooser"
        :header="$t(titleI18n)"
        :cancellable="true"
        @cancel="$emit('cancel')"
        @confirm="$emit('confirm', selectedItems)">
        <loading-cover :loading="!items">
            <component v-if="filters && items"
              :is="filters"
              :items="items"
              @filter-function="filterFunction = $event; filter()"
              @compare-function="compareFunction = $event; filter()"
              @filter="filter()"></component>
              <square-links-grid :count="5">
                <div v-for="item in filteredItems"
                  :key="item.id">
                  <component :is="tile"
                    :class="isSelected(item) ? 'selected' : ''"
                    :no-link="true"
                    @click="onItemClick(item)"
                    :model="item"/>
                </div>
              </square-links-grid>
        </loading-cover>
    </modal>
</template>

<script>
  import {api} from "@/api/api.js";
  import LoadingCover from "@/common/gui/loaders/loading-cover.vue";
  import Modal from "@/common/modal.vue";
  import {debounce} from "lodash";
  import SquareLinksGrid from "@/common/tiles/square-links-grid.vue";

  export default {
      components: {SquareLinksGrid, Modal, LoadingCover},
        props: ['selected', 'titleI18n', 'tile', 'filters', 'endpoint'],
        data() {
            return {
                items: undefined,
                selectedIds: [],
                filteredItems: undefined,
                filterFunction: () => true,
                compareFunction: () => -1,
            };
        },
        mounted() {
          api.get(this.endpoint).then(response => this.items = response.body);
          if (this.selected) {
            this.selectedIds = this.selected.map(item => item.id);
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
          },
          isSelected(item) {
            return this.selectedIds.includes(item.id);
          },
        },
        computed: {
          selectedItems() {
            return this.items.filter(item => this.isSelected(item));
          }
        },
        watch: {
            selected() {
                this.selectedItems = this.selected;
            }
        }
    };
</script>

<style lang="scss">
    @import "../../styles/variables";

    .square-modal-chooser {
        .modal-container {
            max-width: initial;
        }
    }
</style>

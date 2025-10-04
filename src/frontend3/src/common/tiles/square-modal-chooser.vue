<template>
    <modal class="square-modal-chooser"
        :header="$t(titleI18n)"
        :cancellable="true"
        @cancel="$emit('cancel')"
        @confirm="$emit('confirm', selectedItems)">
        <loading-cover :loading="!items">
            <square-links-carousel-with-filters
                :tile="tile"
                :filters="filters"
                :items="items"
                :selected="selectedItems"
                :no-links="true"
                @select="selectedItems = $event"></square-links-carousel-with-filters>
        </loading-cover>
    </modal>
</template>

<script>
  import SquareLinksCarouselWithFilters from "./square-links-carousel-with-filters";
  import {api} from "@/api/api.js";
  import LoadingCover from "@/common/gui/loaders/loading-cover.vue";
  import Modal from "@/common/modal.vue";

  export default {
      components: {Modal, LoadingCover, SquareLinksCarouselWithFilters},
        props: ['selected', 'titleI18n', 'tile', 'filters', 'endpoint'],
        data() {
            return {
                items: undefined,
                selectedItems: this.selected,
            };
        },
        mounted() {
          api.get(this.endpoint).then(response => this.items = response.body);
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

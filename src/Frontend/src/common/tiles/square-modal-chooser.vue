<template>
    <modal class="square-modal-chooser"
        :header="$t(title)"
        cancellable="true"
        @cancel="$emit('cancel')"
        @confirm="$emit('confirm', selectedItems)">
        <loading-cover :loading="!items">
            <square-links-carousel-with-filters
                :square-links-height="squareLinksHeight"
                :tile="tile"
                :filters="filters"
                :items="items"
                :selected="selectedItems"
                @select="selectedItems = $event"></square-links-carousel-with-filters>
        </loading-cover>
    </modal>
</template>

<script>
    import SquareLinksCarouselWithFilters from "./square-links-carousel-with-filters";

    export default {
        components: {SquareLinksCarouselWithFilters},
        props: ['selected', 'title', 'tile', 'filters', 'endpoint', 'squareLinksHeight'],
        data() {
            return {
                items: undefined,
                selectedItems: this.selected,
            };
        },
        mounted() {
            this.$http.get(this.endpoint).then(response => this.items = response.body);
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

<template>
    <modal class="square-modal-chooser"
        :header="$t(title)">
        <loading-cover :loading="!items">
            <square-links-carousel-with-filters
                :tile="tile"
                :filters="filters"
                :items="items"
                :selected="selectedItems"
                @select="selectedItems = $event"></square-links-carousel-with-filters>
        </loading-cover>
        <div slot="footer">
            <a @click="$emit('cancel')"
                class="cancel">
                <i class="pe-7s-close"></i>
            </a>
            <a class="confirm"
                @click="$emit('confirm', selectedItems)">
                <i class="pe-7s-check"></i>
            </a>
        </div>
    </modal>
</template>

<script>
    import SquareLinksCarouselWithFilters from "./square-links-carousel-with-filters";

    export default {
        components: {SquareLinksCarouselWithFilters},
        props: ['selected', 'title', 'tile', 'filters', 'endpoint'],
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
        .modal-footer {
            .cancel {
                color: $supla-grey-light;
            }
        }
    }
</style>

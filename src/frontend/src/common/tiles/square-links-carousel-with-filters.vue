<template>
    <div>
        <component v-if="filters && items"
            :is="filters"
            :items="items"
            @filter-function="filterFunction = $event; filter()"
            @compare-function="compareFunction = $event; filter()"
            @filter="filter()"></component>
        <square-links-carousel v-if="filteredItems"
            :tile="tile"
            :items="filteredItems"
            :selected="selected"
            :no-links="noLinks"
            @select="$emit('select', $event)"
            :new-item-tile="filteredItems.length === items.length ? newItemTile : ''"></square-links-carousel>
    </div>
</template>

<script>
    import SquareLinksCarousel from "./square-links-carousel";
    import {debounce} from "lodash";

    export default {
        components: {SquareLinksCarousel},
        props: ['items', 'selected', 'tile', 'newItemTile', 'filters', 'noLinks'],
        data() {
            return {
                filteredItems: undefined,
                filterFunction: () => true,
                compareFunction: () => -1,
            };
        },
        mounted() {
            this.filter();
        },
        methods: {
            filter: debounce(function () {
                this.filteredItems = this.items ? this.items.filter(this.filterFunction) : this.items;
                if (this.filteredItems) {
                    this.filteredItems = this.filteredItems.sort(this.compareFunction);
                }
            }, 50),
        },
        watch: {
            items() {
                this.filter();
            }
        }
    };
</script>

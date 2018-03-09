<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1>{{ $t(header) }}</h1>
                    <loading-cover :loading="!items">
                        <div v-if="items">
                            <component v-if="filters"
                                :is="filters"
                                :items="items"
                                @filter-function="filterFunction = $event"
                                @filter="filter()"></component>
                            <square-links-carousel
                                :tile="tile"
                                :items="filteredItems"
                                :selected="item"
                                @select="itemChanged"
                                :new-item-tile="filteredItems.length === items.length ? createNewLabel : ''"></square-links-carousel>
                        </div>
                    </loading-cover>
                </div>
            </div>
        </div>
        <hr v-if="item">
        <component :is="details"
            v-if="item"
            :model="item"
            @add="onItemAdded($event)"
            @delete="onItemDeleted()"
            @update="onItemUpdated($event)">
        </component>
    </div>
</template>

<script>
    import BtnFilters from "src/common/btn-filters";
    import SquareLinksCarousel from "src/common/tiles/square-links-carousel";

    export default {
        props: ['header', 'tile', 'details', 'filters', 'endpoint', 'createNewLabel', 'selectedId'],
        components: {SquareLinksCarousel, BtnFilters},
        data() {
            return {
                item: undefined,
                items: undefined,
                filteredItems: undefined,
                filterFunction: () => true,
            };
        },
        mounted() {
            this.$http.get(this.endpoint)
                .then(({body}) => {
                    this.items = body;
                    this.filter();
                    if (this.selectedId) {
                        const selected = this.items.find(item => item.id == this.selectedId);
                        if (selected) {
                            this.itemChanged(selected);
                        }
                    }
                });
        },
        methods: {
            itemChanged(item) {
                this.item = item;
            },
            filter() {
                this.filteredItems = this.items.filter(this.filterFunction);
            },
            onItemAdded(item) {
                this.items.push(item);
                this.item = item;
                this.filter();
            },
            onItemUpdated(item) {
                const itemToUpdate = this.items.find(c => item.id == c.id);
                $.extend(itemToUpdate, item);
                this.filter();
            },
            onItemDeleted() {
                this.items.splice(this.items.indexOf(this.item), 1);
                this.item = undefined;
                this.filter();
            }
        }
    };
</script>

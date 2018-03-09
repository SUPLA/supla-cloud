<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1>{{ $t(header) }}</h1>
                    <loading-cover :loading="!items">
                        <div v-if="items">
                            <square-links-carousel-with-filters
                                :filters="filters"
                                :tile="tile"
                                :items="items"
                                :selected="item"
                                @select="itemChanged"
                                :new-item-tile="createNewLabel"></square-links-carousel-with-filters>
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
    import SquareLinksCarouselWithFilters from "../tiles/square-links-carousel-with-filters";

    export default {
        props: ['header', 'tile', 'details', 'filters', 'endpoint', 'createNewLabel', 'selectedId'],
        components: {SquareLinksCarouselWithFilters},
        data() {
            return {
                item: undefined,
                items: undefined
            };
        },
        mounted() {
            this.$http.get(this.endpoint)
                .then(({body}) => {
                    this.items = body;
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
            onItemAdded(item) {
                this.items.push(item);
                this.item = item;
            },
            onItemUpdated(item) {
                const itemToUpdate = this.items.find(c => item.id == c.id);
                $.extend(itemToUpdate, item);
            },
            onItemDeleted() {
                this.items.splice(this.items.indexOf(this.item), 1);
                this.item = undefined;
            }
        }
    };
</script>

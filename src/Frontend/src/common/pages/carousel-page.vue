<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1 class="carousel-title"
                        v-title>{{ $t(header) }}</h1>
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
        <router-view v-if="items"
            @add="onItemAdded($event)"
            @delete="onItemDeleted()"
            @update="onItemUpdated($event)"
            :item="item"></router-view>
    </div>
</template>

<script>
    import SquareLinksCarouselWithFilters from "../tiles/square-links-carousel-with-filters";
    import {warningNotification} from "../notifier";

    export default {
        props: ['header', 'tile', 'filters', 'endpoint', 'createNewLabel', 'detailsRoute', 'listRoute', 'limit'],
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
                    if (this.$route.params.id) {
                        const selected = this.items.find(item => item.id == this.$route.params.id);
                        if (selected) {
                            this.itemChanged(selected);
                        }
                    }
                });
        },
        methods: {
            itemChanged(item) {
                if (!item.id) {
                    if (this.limit && this.items.length >= this.limit) {
                        return warningNotification('Error', 'Limit has been exceeded', this);
                    }
                    this.$router.push({name: this.detailsRoute, params: {id: 'new'}});
                }
                this.item = item;
            },
            onItemAdded(item) {
                this.items.push(item);
                this.item = item;
                this.$router.push({name: this.detailsRoute, params: {id: item.id}});
            },
            onItemUpdated(item) {
                const itemToUpdate = this.items.find(c => item.id == c.id);
                $.extend(itemToUpdate, item);
            },
            onItemDeleted() {
                this.items.splice(this.items.indexOf(this.item), 1);
                this.item = undefined;
                this.$router.push({name: this.listRouteName});
            }
        },
        computed: {
            listRouteName() {
                return this.listRoute || this.detailsRoute + 's';
            }
        }
    };
</script>

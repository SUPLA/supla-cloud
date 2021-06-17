<template>
    <div>
        <div v-if="item">
            <div class="container">
                <div class="row">
                    <div class="col-xs-12">
                        <h1 class="carousel-title"
                            v-title>
                            <router-link :to="{name: listRouteName}">{{ $t(headerI18n) }}</router-link>
                        </h1>
                        <loading-cover :loading="!items">
                            <div v-if="items">
                                <square-links-carousel-with-filters
                                    :filters="filters"
                                    :tile="tile"
                                    :items="items"
                                    :selected="item"
                                    @select="itemChanged"
                                    :new-item-tile="createNewLabelI18n"></square-links-carousel-with-filters>
                            </div>
                        </loading-cover>
                    </div>
                </div>
            </div>
            <div v-if="item">
                <hr>
                <router-view v-if="items"
                    @add="onItemAdded($event)"
                    @delete="onItemDeleted()"
                    @update="onItemUpdated($event)"
                    :item="item"></router-view>
            </div>
        </div>
        <div v-else>
            <list-page :header-i18n="headerI18n"
                :tile="tile"
                :endpoint="endpoint"
                :create-new-label-i18n="createNewLabelI18n"
                :limit="limit"
                :filters="filters"
                :details-route="detailsRoute"></list-page>
        </div>
    </div>
</template>

<script>
    import SquareLinksCarouselWithFilters from "../tiles/square-links-carousel-with-filters";
    import {warningNotification} from "../notifier";
    import ListPage from "./list-page";
    import $ from "jquery";

    export default {
        props: ['headerI18n', 'tile', 'filters', 'endpoint', 'createNewLabelI18n', 'detailsRoute', 'listRoute', 'limit'],
        components: {ListPage, SquareLinksCarouselWithFilters},
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
                        } else if (this.$route.params.id === 'new') {
                            this.itemChanged({});
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
        },
        watch: {
            '$route.params.id'() {
                if (this.$route.params.id) {
                    let selected = this.items.find(item => item.id == this.$route.params.id);
                    if (!selected) {
                        selected = {};
                    }
                    this.itemChanged(selected);
                } else {
                    this.item = undefined;
                }
            }
        }
    };
</script>

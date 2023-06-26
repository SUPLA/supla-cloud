<template>
    <div>
        <div v-if="item || permanentCarouselView">
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
                    :key="`carousel_page_details_${item.id || 'noid'}`"
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
        props: {
            headerI18n: String,
            tile: String,
            filters: String,
            endpoint: String,
            createNewLabelI18n: String,
            detailsRoute: String,
            listRoute: String,
            limit: Number,
            permanentCarouselView: Boolean,
            idParamName: {
                type: String,
                default: 'id',
            },
            newItemFactory: {
                type: Function,
                default: () => ({}),
            }
        },
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
                    if (this.$route.params[this.idParamName]) {
                        const selected = this.items.find(item => item.id == this.$route.params[this.idParamName]);
                        if (selected) {
                            this.itemChanged(selected);
                        } else if (this.$route.params[this.idParamName] === 'new') {
                            this.itemChanged(this.newItemFactory());
                        }
                    }
                });
        },
        methods: {
            itemChanged(item) {
                console.log(item);
                if (!item.id) {
                    if (this.limit && this.items.length >= this.limit) {
                        return warningNotification('Error', 'Limit has been exceeded', this);
                    }
                    item = this.newItemFactory();
                    this.$router.push({name: this.detailsRoute, params: {[this.idParamName]: 'new'}}).catch(() => undefined);
                }
                this.item = item;
            },
            onItemAdded(item) {
                this.items.push(item);
                this.item = item;
                this.$router.push({name: this.detailsRoute, params: {[this.idParamName]: item.id}});
                this.$emit('count', this.items.length);
            },
            onItemUpdated(item) {
                const itemToUpdate = this.items.find(c => item.id == c.id);
                $.extend(itemToUpdate, item);
            },
            onItemDeleted() {
                this.items.splice(this.items.indexOf(this.item), 1);
                this.item = undefined;
                this.$router.push({name: this.listRouteName});
                this.$emit('count', this.items.length);
            }
        },
        computed: {
            listRouteName() {
                return this.listRoute || this.detailsRoute + 's';
            }
        },
        watch: {
            '$route.params': {
                handler() {
                    if (this.$route.params[this.idParamName]) {
                        let selected = this.items.find(item => item.id == this.$route.params[this.idParamName]);
                        if (!selected) {
                            selected = {};
                        }
                        this.itemChanged(selected);
                    } else {
                        this.item = undefined;
                    }
                },
                deep: true,
            }
        }
    };
</script>

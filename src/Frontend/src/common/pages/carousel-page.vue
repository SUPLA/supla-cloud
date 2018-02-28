<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1>{{ $t(header) }}</h1>
                    <loading-cover :loading="!items">
                        <div v-if="items">
                            <div class="grid-filters"
                                v-if="items.length">
                                <btn-filters v-model="filters.enabled"
                                    :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
                                <input type="text"
                                    class="form-control"
                                    v-model="filters.search"
                                    :placeholder="$t('Search')">
                            </div>
                            <div class="form-group">
                                <square-links-carousel
                                    :tile="tile"
                                    :items="filteredItems"
                                    :selected="item"
                                    @select="itemChanged"
                                    :new-item-tile="filteredItems.length === items.length ? createNewLabel : ''"></square-links-carousel>
                            </div>
                        </div>
                    </loading-cover>
                </div>
            </div>
        </div>
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
    import latinize from "latinize";
    import Vue from "vue";

    export default {
        props: ['header', 'tile', 'details', 'endpoint', 'createNewLabel'],
        components: {SquareLinksCarousel, BtnFilters},
        data() {
            return {
                item: undefined,
                items: undefined,
                filters: {
                    enabled: undefined,
                    search: '',
                }
            };
        },
        computed: {
            filteredItems() {
                let items = this.items;
                if (this.filters.enabled !== undefined) {
                    items = items.filter(item => item.enabled == this.filters.enabled);
                }
                if (this.filters.search) {
                    items = items.filter(item => item.searchString.indexOf(latinize(this.filters.search).toLowerCase()) >= 0);
                }
                return items;
            },
        },
        mounted() {
            this.$http.get(this.endpoint)
                .then(({body}) => this.items = body)
                .then(() => Vue.nextTick(() => this.calculateSearchStrings()));
        },
        methods: {
            itemChanged(item) {
                this.item = item;
            },
            calculateSearchStrings() {
                for (let item of this.items) {
                    const searchString = [item.id, item.caption].join(' ');
                    this.$set(item, 'searchString', latinize(searchString).toLowerCase());
                }
            },
            onItemAdded(item) {
                this.items.push(item);
                this.item = item;
                this.calculateSearchStrings();
            },
            onItemUpdated(item) {
                const itemToUpdate = this.items.find(c => item.id == c.id);
                $.extend(itemToUpdate, item);
                this.calculateSearchStrings();
            },
            onItemDeleted() {
                this.items.splice(this.items.indexOf(this.item), 1);
                this.item = undefined;
                this.calculateSearchStrings();
            }
        }
    };
</script>

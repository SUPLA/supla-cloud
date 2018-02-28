<template>
    <div class="square-links-carousel square-links-height-160"
        v-if="items">
        <carousel v-if="newItemTile || items.length > 0"
            :navigation-enabled="true"
            :pagination-enabled="false"
            navigation-next-label="&gt;"
            navigation-prev-label="&lt;"
            :per-page-custom="[[1024, 4], [768, 3], [600, 2], [100, 1]]"
            ref="carousel">
            <slide v-if="newItemTile">
                <square-link :class="'clearfix pointer lift-up black ' + (selectedItem == newItem ? ' selected' : '')">
                    <a class="valign-center text-center"
                        @click="onItemClick(newItem)">
                        <span>
                            <i class="pe-7s-plus"></i>
                            {{ $t(newItemTile) }}
                        </span>
                    </a>
                </square-link>
            </slide>
            <slide v-for="item in items"
                :key="item.id">
                <component :is="tile"
                    :class="selectedItem && selectedItem.id == item.id ? 'selected' : ''"
                    @click="onItemClick(item)"
                    :model="item"></component>
            </slide>
        </carousel>
        <empty-list-placeholder v-else></empty-list-placeholder>
    </div>
</template>

<script>
    import {Carousel, Slide} from 'vue-carousel';
    import EmptyListPlaceholder from "src/common/gui/empty-list-placeholder";
    import Vue from "vue";

    export default {
        components: {Carousel, Slide, EmptyListPlaceholder},
        props: ['items', 'selected', 'tile', 'newItemTile'],
        data() {
            return {
                selectedItem: undefined,
                newItem: {}
            };
        },
        mounted() {
            this.updateSelectedItem();
        },
        methods: {
            onItemClick(item) {
                this.selectedItem = item;
                this.$emit('select', item == this.newItem ? {} : item);
            },
            updateSelectedItem() {
                if (this.items && this.selectedItem != this.selected && (!this.selected || this.selected.id)) {
                    this.selectedItem = this.selected ? this.items.find(item => item.id == this.selected.id) : undefined;
                    if (this.selectedItem) {
                        Vue.nextTick(() => {
                            const index = this.items.indexOf(this.selectedItem);
                            const desiredPage = Math.max(0, index - this.$refs.carousel.perPage + 1);
                            this.$refs.carousel.goToPage(desiredPage);
                        });
                    }
                }
            }
        },
        watch: {
            selected() {
                this.updateSelectedItem();
            },
            items() {
                this.updateSelectedItem();
            }
        }
    };
</script>

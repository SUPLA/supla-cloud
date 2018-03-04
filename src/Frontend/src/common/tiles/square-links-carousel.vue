<template>
    <div class="square-links-carousel square-links-height-160"
        v-if="items">
        <carousel v-if="newItemTile || items.length > 0"
            :navigation-enabled="true"
            :pagination-enabled="false"
            navigation-next-label="&gt;"
            navigation-prev-label="&lt;"
            :per-page-custom="[[1024, 4], [768, 3], [600, 2], [1, 1]]"
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
                console.log(this.$refs.carousel);
                this.selectedItem = item;
                this.$emit('select', item == this.newItem ? {} : item);
            },
            updateSelectedItem() {
                if (this.items && this.selectedItem != this.selected && (!this.selected || this.selected.id)) {
                    this.selectedItem = this.selected ? this.items.find(item => item.id == this.selected.id) : undefined;
                    if (this.selectedItem) {
                        Vue.nextTick(() => {
                            const index = this.items.indexOf(this.selectedItem);
                            const desiredPage = Math.max(0, Math.min(this.$refs.carousel.pageCount, index - this.$refs.carousel.perPage + 2));
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

<style lang="scss">
    @import '../../styles/variables';
    @import '../../styles/mixins';

    .VueCarousel {
        .VueCarousel-navigation {
            &--disabled {
                visibility: hidden;
            }
        }
        .VueCarousel-navigation-button {
            background: black;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            color: white !important;
            text-align: center;
            font-size: 2em;
            line-height: 1.1em;
            font-family: 'Quicksand';
            &.VueCarousel-navigation-prev {
                left: -5px;
            }
            &.VueCarousel-navigation-next {
                right: -5px;
            }
            @include on-xs-and-down {
                position: static !important;
                top: initial;
                height: 25px;
                width: 25px;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent;
                color: $supla-grey-dark !important;
                transform: none !important;
                &.VueCarousel-navigation-prev {
                    float: left;
                }
                &.VueCarousel-navigation-next {
                    float: right;
                }
            }
        }
        .VueCarousel-slide {
            padding: 5px;
        }
        h2 {
            margin-top: 3px;
        }
    }
</style>

<template>
    <div class="square-links-carousel"
        v-if="items">
        <a class="btn btn-block btn-black text-center visible-xs"
            @click="onItemClick(newItem)"
            v-if="newItemTile && !$frontendConfig.maintenanceMode">
            <span>
                <i class="pe-7s-plus"></i>
                {{ $t(newItemTile) }}
            </span>
        </a>
        <carousel v-if="newItemTile || items.length > 0"
            :navigation-enabled="true"
            :pagination-enabled="false"
            navigation-next-label="&gt;"
            navigation-prev-label="&lt;"
            :per-page-custom="[[1024, 4], [768, 3], [600, 2], [1, 1]]"
            ref="carousel">
            <slide v-if="showNewItemTile">
                <square-link class="clearfix pointer lift-up black">
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
                    :class="isSelected(item) ? 'selected' : ''"
                    :no-link="noLinks"
                    @click="onItemClick(item)"
                    :model="item"></component>
            </slide>
        </carousel>
        <empty-list-placeholder v-else></empty-list-placeholder>
    </div>
</template>

<script>
    import {Carousel, Slide} from 'vue-carousel';
    import EmptyListPlaceholder from "../../common/gui/empty-list-placeholder";
    import Vue from "vue";

    export default {
        components: {Carousel, Slide, EmptyListPlaceholder},
        props: ['items', 'selected', 'tile', 'newItemTile', 'noLinks'],
        data() {
            return {
                selectedIds: [],
                newItem: {}
            };
        },
        mounted() {
            this.updateSelectedItem();
        },
        computed: {
            multiple() {
                return Array.isArray(this.selected);
            },
            showNewItemTile() {
                return this.newItemTile && this.$mq.resize && this.$mq.above(this.$mv.xs) && !this.$frontendConfig.maintenanceMode;
            },
            selectedItemIndex() {
                return !this.multiple && this.selected && this.items.findIndex(item => this.isSelected(item));
            },
        },
        methods: {
            onItemClick(item) {
                if (this.multiple) {
                    if (this.isSelected(item)) {
                        this.selectedIds.splice(this.selectedIds.indexOf(item.id), 1);
                    } else {
                        this.selectedIds.push(item.id);
                    }
                } else {
                    this.selectedIds = [item.id];
                }
                const selectedItems = this.items.filter(item => this.isSelected(item));
                this.$emit('select', item == this.newItem ? {} : (this.multiple ? selectedItems : selectedItems[0]));
            },
            isSelected(item) {
                return this.selectedIds.indexOf(item.id) >= 0;
            },
            updateSelectedItem() {
                if (this.multiple) {
                    this.selectedIds = this.selected.map(item => item.id || item);
                } else if (this.selected && !this.isSelected(this.selected)) {
                    this.selectedIds = [this.selected.id];
                }
            }
        },
        watch: {
            selected() {
                this.updateSelectedItem();
            },
            items() {
                this.updateSelectedItem();
            },
            selectedItemIndex() {
                if (this.selected && !this.multiple) {
                    Vue.nextTick(() => {
                        const index = this.items.findIndex(item => this.isSelected(item));
                        let desiredPage = index - this.$refs.carousel.perPage + 2;
                        if (this.showNewItemTile) {
                            ++desiredPage;
                        }
                        desiredPage = Math.max(0, Math.min(this.$refs.carousel.pageCount, desiredPage));
                        this.$refs.carousel.goToPage(desiredPage);
                    });
                }
            },
        }
    };
</script>

<style lang="scss">
    @import '../../styles/variables';
    @import '../../styles/mixins';

    @mixin carousel-navigation-below {
        .VueCarousel {
            .VueCarousel-navigation {
                display: flex;
                justify-content: space-between;
                padding: 0 5px;
                margin-top: -5px;
                margin-bottom: 10px;
            }
            .VueCarousel-navigation-button {
                position: static !important;
                top: initial;
                height: auto;
                width: auto;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent;
                color: $supla-grey-dark !important;
                transform: none !important;
            }
        }
    }

    .VueCarousel {
        margin: 0 -5px;
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

        }
        .VueCarousel-slide {
            padding: 5px;
        }
        h2 {
            margin-top: 3px;
        }
    }

    .modal-body {
        @include carousel-navigation-below;
    }

    @include on-sm-and-down {
        @include carousel-navigation-below;
    }
</style>

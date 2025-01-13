<template>
    <div class="square-links-carousel"
        v-if="items">
        <a class="btn btn-block btn-black text-center visible-xs"
            @click="onItemClick(newItem)"
            v-if="newItemTile && !frontendConfig.maintenanceMode">
            <span>
                <i class="pe-7s-plus"></i>
                {{ $t(newItemTile) }}
            </span>
        </a>
        <carousel v-if="newItemTile || items.length > 0"
            :navigation-enabled="true"
            :pagination-enabled="false"
            :scroll-per-page="false"
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
                    :model="item"/>
            </slide>
        </carousel>
        <empty-list-placeholder v-else/>
    </div>
</template>

<script>
    import {Carousel, Slide} from 'vue-carousel';
    import EmptyListPlaceholder from "../../common/gui/empty-list-placeholder";
    import Vue from "vue";
    import {useMediaQuery} from "@vueuse/core";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        components: {Carousel, Slide, EmptyListPlaceholder},
        props: ['items', 'selected', 'tile', 'newItemTile', 'noLinks'],
        data() {
            return {
                selectedIds: [],
                newItem: {},
                isXs: useMediaQuery('(max-width: 768px)'),
            };
        },
        mounted() {
            this.updateSelectedItem();
            // fix for negative page count when only single element is left after filtering
            Object.defineProperty(this.$refs.carousel, "pageCount", {
                get: function () {
                    const pageCount = this.scrollPerPage
                        ? Math.ceil(this.slideCount / this.currentPerPage)
                        : this.slideCount - this.currentPerPage + 1;
                    return pageCount > 0 ? pageCount : (this.slideCount > 0 ? 1 : 0);
                },
            });
        },
        computed: {
            multiple() {
                return Array.isArray(this.selected);
            },
            showNewItemTile() {
                return this.newItemTile && !this.isXs && !this.frontendConfig.maintenanceMode;
            },
            selectedItemIndex() {
                return !this.multiple && this.selected && this.items.findIndex(item => this.isSelected(item));
            },
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
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
                } else if (!this.selected) {
                    this.selectedIds = [];
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
                        if (this.$refs.carousel) {
                            const index = this.items.findIndex(item => this.isSelected(item));
                            let desiredPage = index - this.$refs.carousel.perPage + 2;
                            if (this.showNewItemTile) {
                                ++desiredPage;
                            }
                            desiredPage = Math.max(0, Math.min(this.$refs.carousel.pageCount - 1, desiredPage));
                            this.$refs.carousel.goToPage(desiredPage);
                        }
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
                background-color: transparent !important;
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
            background-color: black !important;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            color: white !important;
            text-align: center;
            font-size: 2em;
            line-height: 1.1em;
            font-family: 'Quicksand' !important;
            padding-top: 3px !important;
            &.VueCarousel-navigation-prev {
                left: -5px;
            }
            &.VueCarousel-navigation-next {
                right: -5px;
            }

        }
        .VueCarousel-slide {
            padding: 5px;
            a:focus {
                color: inherit;
            }
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

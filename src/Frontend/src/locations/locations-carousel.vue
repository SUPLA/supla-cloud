<template>
    <div class="locations-carousel square-links-height-160">
        <carousel :navigation-enabled="true"
            :pagination-enabled="false"
            navigation-next-label="&gt;"
            navigation-prev-label="&lt;"
            :per-page-custom="[[1024, 4], [768, 3], [600, 2], [100, 1]]"
            ref="carousel">
            <slide v-if="showNewButton">
                <square-link :class="'clearfix pointer lift-up black ' + (selectedLocation == newLocation ? ' selected' : '')">
                    <a class="valign-center text-center"
                        @click="onLocationClick(newLocation)">
                        <span>
                            <i class="pe-7s-plus"></i>
                            {{ $t('Create New Location') }}
                        </span>
                    </a>
                </square-link>
            </slide>
            <slide v-for="location in locations"
                :key="location.id">
                <square-link :class="'clearfix pointer lift-up ' + (location.enabled ? '' : 'grey ') + (selectedLocation && selectedLocation.id == location.id ? 'selected' : '')">
                    <a @click="onLocationClick(location)">
                        <location-tile-content :location="location"></location-tile-content>
                    </a>
                </square-link>
            </slide>
        </carousel>
    </div>
</template>

<script>
    import {Carousel, Slide} from 'vue-carousel';
    import Vue from "vue";
    import FunctionIcon from "../channels/function-icon.vue";
    import LocationTileContent from "./location-tile-content";

    export default {
        components: {
            LocationTileContent,
            FunctionIcon, Carousel, Slide
        },
        props: ['locations', 'location', 'showNewButton'],
        data() {
            return {
                selectedLocation: undefined,
                newLocation: {}
            };
        },
        mounted() {
            this.updateSelectedLocation();
        },
        methods: {
            onLocationClick(location) {
                this.selectedLocation = location;
                this.$emit('select', location == this.newLocation ? {} : location);
            },
            updateSelectedLocation() {
                if (this.selectedLocation != this.location && (!this.location || this.location.id)) {
                    this.selectedLocation = this.locations.find(loc => loc.id == this.location.id);
                    if (this.selectedLocation) {
                        Vue.nextTick(() => {
                            const index = this.locations.indexOf(this.selectedLocation);
                            const desiredPage = Math.max(0, index - this.$refs.carousel.perPage + 1);
                            this.$refs.carousel.goToPage(desiredPage);
                        });
                    }
                }
            }
        },
        watch: {
            location() {
                this.updateSelectedLocation();
            }
        }
    };
</script>

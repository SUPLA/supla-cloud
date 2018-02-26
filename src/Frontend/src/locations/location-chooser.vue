<template>
    <modal class="modal-location-chooser"
        :header="$t('Choose a location')">
        <loading-cover :loading="!locations">
            <carousel :navigation-enabled="true"
                :pagination-enabled="false"
                navigation-next-label="&gt;"
                navigation-prev-label="&lt;"
                :per-page-custom="[[1024, 4], [768, 3], [600, 2], [100, 1]]"
                ref="carousel">
                <slide v-for="location in locations"
                    :key="location.id">
                    <square-link :class="'clearfix pointer lift-up ' + (location.enabled ? '' : 'grey ') + (selectedLocation.id == location.id ? 'selected' : '')">
                        <a @click="selectedLocation = location">
                            <location-tile-content :location="location"></location-tile-content>
                        </a>
                    </square-link>
                </slide>
            </carousel>
        </loading-cover>
        <div slot="footer">
            <a @click="$emit('cancel')"
                class="cancel">
                <i class="pe-7s-close"></i>
            </a>
            <a class="confirm"
                @click="$emit('confirm', selectedLocation)">
                <i class="pe-7s-check"></i>
            </a>
        </div>
    </modal>
</template>

<script>
    import {Carousel, Slide} from 'vue-carousel';
    import LocationTileContent from "./location-tile-content.vue";

    export default {
        components: {Carousel, Slide, LocationTileContent},
        props: ['currentLocation'],
        data() {
            return {
                locations: undefined,
                selectedLocation: this.currentLocation || {},
            };
        },
        mounted() {
            this.$http.get('locations').then(response => {
                this.locations = response.body;
            });
        },
        watch: {
            currentLocation() {
                this.selectedLocation = this.currentLocation || {};
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .modal-location-chooser {
        .modal-container {
            max-width: initial;
        }
        .modal-footer {
            .cancel {
                color: $supla-grey-light;
            }
        }
    }
</style>

<template>
    <modal class="modal-location-chooser"
        :header="$t('Choose a location')">
        <loading-cover :loading="!locations">
            <locations-carousel v-if="locations"
                :locations="locations"
                :location="currentLocation"
                @select="currentLocation = $event"></locations-carousel>
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
    import LocationsCarousel from "./locations-carousel";

    export default {
        components: {
            LocationsCarousel,
            Carousel, Slide, LocationTileContent
        },
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

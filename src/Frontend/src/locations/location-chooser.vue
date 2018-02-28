<template>
    <modal class="modal-location-chooser"
        :header="$t('Choose a location')">
        <loading-cover :loading="!locations">
            <square-links-carousel
                tile="location-tile"
                :items="locations"
                :selected="currentLocation"
                @select="selectedLocation = $event"></square-links-carousel>
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
    import LocationTileContent from "./location-tile-content";
    import SquareLinksCarousel from "../common/tiles/square-links-carousel";
    import LocationTile from "./location-tile";
    import Vue from "vue";

    Vue.component('LocationTile', LocationTile);

    export default {
        components: {SquareLinksCarousel, LocationTileContent},
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

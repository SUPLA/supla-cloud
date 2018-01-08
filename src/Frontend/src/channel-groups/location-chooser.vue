<template>
    <modal class="modal-location-chooser"
        :header="$t('Chose a location')">
        <loading-cover :loading="!locations">
            <carousel :navigation-enabled="true"
                :pagination-enabled="false"
                navigation-next-label="&gt;"
                navigation-prev-label="&lt;"
                :per-page-custom="[[1024, 4], [768, 3], [600, 2], [400, 1]]"
                ref="carousel">
                <slide v-for="location in locations"
                    :key="location.id">
                    <square-link :class="'clearfix pointer lift-up ' + (location.enabled ? '' : 'grey ') + (selectedLocation.id == location.id ? 'selected' : '')">
                        <a @click="selectedLocation = location">
                            <h2>ID<strong>{{ location.id }} </strong></h2>
                            <dl>
                                <dd>{{ $t('Devices no') }}</dd>
                                <dt>{{ location.iodeviceIds.length }}</dt>
                            </dl>
                            <div v-if="location.caption">
                                <div class="separator"></div>
                                {{ location.caption }}
                            </div>
                            <div class="square-link-label">
                                <span :class="'label label-' + (location.enabled ? 'success' : 'grey')">
                                    {{ $t(location.enabled ? 'Enabled' : 'Disabled') }}
                                </span>
                            </div>
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
    import SquareLink from "src/common/square-link.vue";
    import {Carousel, Slide} from 'vue-carousel';

    export default {
        components: {Carousel, Slide, SquareLink},
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

        .modal-footer {
            .cancel {
                color: $supla-grey-light;
            }
        }
    }
</style>

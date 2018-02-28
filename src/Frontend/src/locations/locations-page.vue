<template>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <h1>{{ $t('Locations') }}</h1>
                    <loading-cover :loading="!locations">
                        <div v-if="locations">
                            <div class="grid-filters"
                                v-if="locations.length">
                                <btn-filters v-model="filters.enabled"
                                    :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
                                <input type="text"
                                    class="form-control"
                                    v-model="filters.search"
                                    :placeholder="$t('Search')">
                            </div>
                            <div class="form-group">
                                <square-links-carousel
                                    tile="location-tile"
                                    :items="filteredLocations"
                                    :selected="location"
                                    @select="locationChanged"
                                    :new-item-tile="filteredLocations.length === locations.length ? 'Create New Location' : ''"></square-links-carousel>
                                <empty-list-placeholder v-if="locations.length && filteredLocations.length === 0"></empty-list-placeholder>
                            </div>
                        </div>
                    </loading-cover>
                </div>
            </div>
        </div>
        <location-details v-if="location"
            :model="location"
            @delete="onLocationDeleted()"
            @update="onLocationUpdated($event)">
        </location-details>
    </div>
</template>

<script>
    import BtnFilters from "src/common/btn-filters";
    import LocationDetails from "./location-details";
    import LocationTile from "./location-tile";
    import latinize from "latinize";
    import Vue from "vue";
    import EmptyListPlaceholder from "src/devices/list/empty-list-placeholder";
    import SquareLinksCarousel from "../common/tiles/square-links-carousel";

    Vue.component('LocationTile', LocationTile);

    export default {
        components: {
            SquareLinksCarousel,
            BtnFilters, LocationDetails, LocationTile, EmptyListPlaceholder
        },
        data() {
            return {
                location: undefined,
                locations: undefined,
                filters: {
                    enabled: undefined,
                    search: '',
                }
            };
        },
        computed: {
            filteredLocations() {
                let locations = this.locations;
                if (this.filters.enabled !== undefined) {
                    locations = locations.filter(location => location.enabled == this.filters.enabled);
                }
                if (this.filters.search) {
                    locations = locations.filter(location => location.searchString.indexOf(latinize(this.filters.search).toLowerCase()) >= 0);
                }
                return locations;
            },
        },
        mounted() {
            this.$http.get('locations')
                .then(({body}) => this.locations = body)
                .then(() => Vue.nextTick(() => this.calculateSearchStrings()));
        },
        methods: {
            locationChanged(location) {
                if (location.id) {
                    this.location = location;
                } else {
                    this.$http.post('locations').then(({body}) => this.onLocationAdded(body));
                }
            },
            calculateSearchStrings() {
                for (let location of this.locations) {
                    const searchString = [location.id, location.caption].join(' ');
                    this.$set(location, 'searchString', latinize(searchString).toLowerCase());
                }
            },
            onLocationAdded(location) {
                this.locations.push(location);
                this.location = location;
                this.calculateSearchStrings();
            },
            onLocationUpdated(location) {
                const cg = this.locations.find(c => location.id == c.id);
                $.extend(cg, location);
                this.calculateSearchStrings();
            },
            onLocationDeleted() {
                this.locations.splice(this.locations.indexOf(this.location), 1);
                this.location = undefined;
                this.calculateSearchStrings();
            }
        }
    };
</script>

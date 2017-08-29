<template>
    <div>
        <div class="container">
            <div class="clearfix left-right-header">
                <div>
                    <h1>{{ $t('Client\'s Apps') }}</h1>
                    <h4 class="text-muted">{{ $t('smartphones, tablets, etc.') }}</h4>
                </div>
                <div>
                    <devices-registration-button field="clientsRegistrationEnabled"
                        caption="Registration of new clients"></devices-registration-button>
                </div>
            </div>
        </div>
        <div class="container grid-filters">
            <btn-filters v-model="filters.sort"
                :filters="[{label: $t('A-Z'), value: 'az'}, {label: $t('Last access'), value: 'lastAccess'}]"></btn-filters>
            <btn-filters v-model="filters.enabled"
                :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
            <btn-filters v-model="filters.connected"
                :filters="[{label: $t('All'), value: undefined}, {label: $t('Active'), value: true}, {label: $t('Idle'), value: false}]"></btn-filters>
            <input type="text"
                class="form-control"
                v-model="filters.search"
                :placeholder="$t('Search')">
        </div>
        <square-links-grid v-if="clientApps && filteredClientApps.length"
            :count="filteredClientApps.length"
            class="square-links-height-250">
            <div v-for="app in filteredClientApps"
                :key="app.id"
                :ref="'app-tile-' + app.id">
                <client-app-tile :app="app"
                    :access-ids="accessIds"
                    @change="calculateSearchStrings()"
                    @delete="removeClientFromList(app)"></client-app-tile>
            </div>
        </square-links-grid>
        <empty-list-placeholder v-else-if="clientApps"></empty-list-placeholder>
        <loader-dots v-else></loader-dots>
        <div class="hidden"
            v-if="clientApps">
            <!--allow filtered-out items to still receive status updates-->
            <client-app-connection-status-label :app="app"
                :key="app.id"
                v-for="app in clientApps"></client-app-connection-status-label>
        </div>
    </div>
</template>

<script>
    import Vue from "vue";
    import BtnFilters from "../common/btn-filters.vue";
    import LoaderDots from "../common/loader-dots.vue";
    import SquareLinksGrid from "../common/square-links-grid.vue";
    import ClientAppTile from "./client-app-tile.vue";
    import DevicesRegistrationButton from "src/devices/list/devices-registration-button.vue";
    import ClientAppConnectionStatusLabel from "./client-app-connection-status-label.vue";
    import latinize from "latinize";
    import EmptyListPlaceholder from "src/devices/list/empty-list-placeholder.vue";

    export default {
        components: {
            BtnFilters,
            ClientAppConnectionStatusLabel,
            ClientAppTile,
            DevicesRegistrationButton,
            EmptyListPlaceholder,
            LoaderDots,
            SquareLinksGrid,
        },
        data() {
            return {
                clientApps: undefined,
                accessIds: undefined,
                filters: {
                    sort: 'az',
                    enabled: undefined,
                    connected: undefined,
                    search: '',
                }
            };
        },
        mounted() {
            this.$http.get('client-apps')
                .then(({body}) => this.clientApps = body)
                .then(() => Vue.nextTick(this.calculateSearchStrings()));
            this.$http.get('aid').then(({body}) => this.accessIds = body);
        },
        computed: {
            filteredClientApps() {
                let apps = this.clientApps;
                if (this.filters.enabled !== undefined) {
                    apps = apps.filter(app => app.enabled == this.filters.enabled);
                }
                if (this.filters.connected !== undefined) {
                    apps = apps.filter(app => app.connected == this.filters.connected);
                }
                if (this.filters.search) {
                    apps = apps.filter(app => app.searchString.indexOf(latinize(this.filters.search).toLowerCase()) >= 0);
                }
                if (this.filters.sort == 'az') {
                    apps = apps.sort((a1, a2) => a1.name.toLowerCase() < a2.name.toLowerCase() ? -1 : 1);
                } else if (this.filters.sort == 'lastAccess') {
                    apps = apps.sort((a1, a2) => moment(a2.lastAccessDate).diff(moment(a1.lastAccessDate)));
                }
                return apps;
            }
        },
        methods: {
            calculateSearchStrings() {
                for (let app of this.clientApps) {
                    const ref = this.$refs['app-tile-' + app.id];
                    if (ref && ref.length) {
                        this.$set(app, 'searchString', latinize(ref[0].innerText).toLowerCase());
                    }
                }
            },
            removeClientFromList(app) {
                this.clientApps.splice(this.clientApps.indexOf(app), 1);
            }
        }
    };
</script>

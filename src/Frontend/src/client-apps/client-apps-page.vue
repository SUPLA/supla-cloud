<template>
    <div>
        <div class="container">
            <div class="clearfix">
                <div class="client-apps-headers">
                    <h1>{{ $t('Client Apps') }}</h1>
                    <h4 class="text-muted">{{ $t('smartphones, tables, etc.') }}</h4>
                </div>
                <div class="client-apps-registration-button">
                    <client-apps-registration-button></client-apps-registration-button>
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
            class="square-links-height-240">
            <div v-for="app in filteredClientApps"
                :key="app.id">
                <client-app-tile :app="app"
                    :access-ids="accessIds"
                    @delete="removeClientFromList(app)"></client-app-tile>
            </div>
        </square-links-grid>
        <div v-else-if="clientApps"
            class="text-center">
            <h3><i class="pe-7s-paint-bucket"></i></h3>
            <h2>Pusto!</h2>
        </div>
        <loader-dots v-else></loader-dots>
        <div class="hidden"
            v-if="clientApps">
            <!--prevents filtered-out items from receiving status updates-->
            <client-app-connection-status-label :app="app"
                :key="app.id"
                v-for="app in clientApps"></client-app-connection-status-label>
        </div>
    </div>
</template>

<style lang="scss">
    @import "../styles/mixins";

    .client-apps-headers {
        float: left;
    }

    .client-apps-registration-button {
        float: right;
        @include on-xs-and-down {
            float: none;
            clear: both;
            margin: 5px auto;
            text-align: center;
        }
    }
</style>


<script>
    import BtnFilters from "../common/btn-filters.vue";
    import LoaderDots from "../common/loader-dots.vue";
    import SquareLinksGrid from "../common/square-links-grid.vue";
    import ClientAppTile from "./client-app-tile.vue";
    import ClientAppsRegistrationButton from "./client-apps-registration-button.vue";
    import ClientAppConnectionStatusLabel from "./client-app-connection-status-label.vue";

    export default {
        components: {BtnFilters, LoaderDots, ClientAppTile, ClientAppsRegistrationButton, SquareLinksGrid, ClientAppConnectionStatusLabel},
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
            this.$http.get('client-apps').then(({body}) => this.clientApps = body);
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
                    apps = apps.filter(app => app.name.toLowerCase().indexOf(this.filters.search.toLowerCase()) >= 0);
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
            removeClientFromList(app) {
                this.clientApps.splice(this.clientApps.indexOf(app), 1);
            }
        }
    };
</script>

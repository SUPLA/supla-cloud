<template>
    <div>
        <div class="container">
            <div class="clearfix">
                <div class="client-apps-headers">
                    <h1 class="no-margin-top">{{ $t('Client Apps') }}</h1>
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
        <div class="container-fluid">
            <square-links-grid v-if="clientApps && filteredClientApps.length">
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
        </div>
    </div>
</template>

<style lang="scss">
    @import "../styles/mixins";

    .grid-filters {
        text-align: right;
        ::-webkit-input-placeholder {
            text-align: center;
        }
        ::-moz-placeholder {
            text-align: center;
        }
        :-ms-input-placeholder {
            text-align: center;
        }
        input[type=text] {
            max-width: 150px;
            display: inline-block;
            margin-left: 5px;
        }
        > * {
            margin-bottom: 5px;
        }
        @include on-xs-and-down {
            text-align: center;
            margin: 5px 0;
        }
    }

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

    export default {
        components: {BtnFilters, LoaderDots, ClientAppTile, ClientAppsRegistrationButton, SquareLinksGrid},
        data() {
            return {
                clientApps: undefined,
                accessIds: undefined,
                timer: null,
                filters: {
                    sort: 'az',
                    enabled: undefined,
                    connected: undefined,
                    search: '',
                }
            };
        },
        mounted() {
            this.$http.get('client-apps').then(({body}) => {
                body.forEach(app => {
                    app.editing = false;
                    app.connected = undefined;
                });
                this.clientApps = body;
                this.fetchConnectedClientApps();
                this.timer = setInterval(this.fetchConnectedClientApps, 7000);
            });
            this.$http.get('aid').then(({body}) => {
                this.accessIds = body;
            });

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
            fetchConnectedClientApps() {
                this.$http.get('client-apps?onlyConnected=true').then(({body}) => {
                    const connectedIds = body.map(app => app.id);
                    this.clientApps.forEach(app => app.connected = connectedIds.indexOf(app.id) >= 0);
                });
            },
            removeClientFromList(app) {
                this.clientApps.splice(this.clientApps.indexOf(app), 1);
            }
        },
        beforeDestroy() {
            clearInterval(this.timer);
        }
    };
</script>

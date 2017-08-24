<template>
    <div>
        <div class="container">
            <div class="clearfix">
                <div class="pull-right">
                    <client-apps-registration-button></client-apps-registration-button>
                </div>
                <h1 class="no-margin-top">{{ $t('Client Apps') }}</h1>
                <h4 class="text-muted">{{ $t('smartphones, tables, etc.') }}</h4>
            </div>
        </div>
        <div class="container text-right">
            <div class="btn-group btn-group-filters">
                <button :class="'btn btn-black btn-outline ' + (enabledFilter === undefined ? 'active' : '')"
                    @click="enabledFilter = undefined">Wszystko
                </button>
                <button :class="'btn btn-black btn-outline ' + (enabledFilter === true ? 'active' : '')"
                    @click="enabledFilter = true">Włączone
                </button>
                <button :class="'btn btn-black btn-outline ' + (enabledFilter === false ? 'active' : '')"
                    @click="enabledFilter = false">Wyłączone
                </button>
            </div>
            <div class="btn-group btn-group-filters">
                <button :class="'btn btn-black btn-outline ' + (sort == 'az' ? 'active' : '')"
                    @click="sort = 'az'">A-Z
                </button>
                <button :class="'btn btn-black btn-outline ' + (sort == 'lastAccess' ? 'active' : '')"
                    @click="sort = 'lastAccess'">Ostatnie połączenie
                </button>
            </div>
            <div class="btn-group btn-group-filters">
                <button class="btn btn-black btn-outline active">Wszystko</button>
                <button class="btn btn-black btn-outline">Aktywne</button>
                <button class="btn btn-black btn-outline">Bezczynne</button>
            </div>
        </div>
        <div class="container-fluid">
            <square-links-grid v-if="clientApps">
                <div v-for="app in filteredClientApps"
                    :key="app.id">
                    <client-app-tile :app="app"
                        :access-ids="accessIds"
                        @delete="removeClientFromList(app)"></client-app-tile>
                </div>
            </square-links-grid>
            <loader-dots v-else></loader-dots>
        </div>
    </div>
</template>

<style lang="scss">
    .btn-group-filters {
        opacity: .6;
        transition: opacity .2s;
        &:hover {
            opacity: 1;
        }
    }
</style>


<script>
    import LoaderDots from "../common/loader-dots.vue";
    import SquareLinksGrid from "../common/square-links-grid.vue";
    import ClientAppTile from "./client-app-tile.vue";
    import ClientAppsRegistrationButton from "./client-apps-registration-button.vue";

    export default {
        components: {LoaderDots, ClientAppTile, ClientAppsRegistrationButton, SquareLinksGrid},
        data() {
            return {
                clientApps: undefined,
                accessIds: undefined,
                timer: null,
                enabledFilter: undefined,
                sort: 'az'
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
                if (this.enabledFilter !== undefined) {
                    apps = apps.filter(app => app.enabled == this.enabledFilter);
                }
                if (this.sort == 'az') {
                    apps = apps.sort((a1, a2) => a1.name.toLowerCase() < a2.name.toLowerCase() ? -1 : 1);
                } else if (this.sort == 'lastAccess') {
                    apps = apps.sort((a1, a2) => moment(a1.lastAccessDate).isBefore(moment(a2.lastAccessDate)) ? 1 : -1);
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

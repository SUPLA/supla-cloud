<template>
    <div>
        <div class="clearfix">
            <div class="pull-right">
                <client-apps-registration-button></client-apps-registration-button>
            </div>
            <h1 class="no-margin-top">{{ $t('Client Apps') }}</h1>
            <h4 class="text-muted">{{ $t('smartphones, tables, etc.') }}</h4>
        </div>
        <div v-if="clientApps"
            class="row">
            <div class="col-lg-4 col-md-6 col"
                v-for="app in clientApps">
                <client-app-tile :app="app"
                    :access-ids="accessIds"
                    @delete="removeClientFromList(app)"></client-app-tile>
            </div>
        </div>
        <loader-dots v-else></loader-dots>
    </div>
</template>

<style scoped>
    .col {
        padding: 10px 5px;
    }
</style>


<script>
    import LoaderDots from "../common/loader-dots.vue";
    import ClientAppTile from "./client-app-tile.vue";
    import ClientAppsRegistrationButton from "./client-apps-registration-button.vue";

    export default {
        components: {LoaderDots, ClientAppTile, ClientAppsRegistrationButton},
        data() {
            return {
                clientApps: undefined,
                accessIds: undefined,
                timer: null,
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

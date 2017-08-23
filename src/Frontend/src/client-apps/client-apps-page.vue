<template>
    <div>
        <div class="clearfix">
            <div class="pull-right">
                <client-apps-registration-button></client-apps-registration-button>
            </div>
            <h1>{{ $t('Client Apps') }}</h1>
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
            };
        },
        mounted() {
            this.$http.get('client-apps').then(({body}) => {
                body.forEach((app) => app.editing = false);
                this.clientApps = body;
            });
            this.$http.get('aid').then(({body}) => {
                this.accessIds = body;
            });
        },
        methods: {
            removeClientFromList(app) {
                this.clientApps.splice(this.clientApps.indexOf(app), 1);
            }
        }
    };
</script>

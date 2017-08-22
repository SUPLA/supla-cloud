<template>
    <div>
        <div v-if="clientApps"
            class="row">
            <div class="col-lg-4 col-md-6 col"
                v-for="app in clientApps">
                <client-app-tile :app="app" :access-ids="accessIds"></client-app-tile>
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

    export default {
        components: {LoaderDots, ClientAppTile},
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
        }
    };
</script>

<template>
    <page-container :error="error">
        <div class="container">
            <h1 v-title>
                SUPLA Apps
                <span v-if="app">
                    &mdash; <span v-title>{{ app.name }}</span>
                </span>
            </h1>
        </div>
        <loading-cover :loading="apps === undefined">
            <div v-if="apps && apps.length">
                <transition name="fade-router">
                    <div v-if="!id && !app">
                        <div v-if="filteredApps.length">
                            <square-links-grid
                                :count="filteredApps.length">
                                <div v-for="app in filteredApps"
                                    :key="app.id">
                                    <public-app-tile :app="app"></public-app-tile>
                                </div>
                            </square-links-grid>
                        </div>
                        <empty-list-placeholder v-else></empty-list-placeholder>
                    </div>
                </transition>
                <transition name="fade-router">
                    <div v-if="app">
                        <div class="container form-group">
                            <router-link :to="{name: 'publicApps'}">
                                &laquo; {{ $t('Back to apps list') }}
                            </router-link>
                        </div>
                        <public-app-details
                            :app="app"></public-app-details>
                    </div>
                </transition>
            </div>
            <div class="container"
                v-if="apps && !apps.length">
                <public-apps-catalog-not-registered-message></public-apps-catalog-not-registered-message>
            </div>
        </loading-cover>
    </page-container>
</template>

<script>
    import PublicAppsCatalogNotRegisteredMessage from "./public-apps-catalog-not-registered-message.vue";
    import PublicAppTile from "./public-app-tile.vue";
    import PublicAppDetails from "./public-app-details.vue";
    import PageContainer from "../../../common/pages/page-container.vue";

    export default {
        props: ['id'],
        components: {PageContainer, PublicAppDetails, PublicAppTile, PublicAppsCatalogNotRegisteredMessage},
        data() {
            return {
                app: undefined,
                apps: undefined,
                error: undefined,
                loadedApp: undefined,
            };
        },
        computed: {
            filteredApps() {
                return this.apps;
            }
        },
        mounted() {
            this.$http.get('public-oauth-apps', {skipErrorHandler: [406]})
                .then(({body: apps}) => this.apps = apps)
                .then(() => this.displayRequestedApp())
                .catch((response) => this.apps = response.status);
        },
        methods: {
            displayRequestedApp() {
                if (this.id) {
                    this.app = this.apps.filter(app => app.id == this.id)[0];
                    if (!this.app) {
                        this.error = 404;
                    }
                } else {
                    this.app = undefined;
                }
            }
        },
        watch: {
            id() {
                this.displayRequestedApp();
            }
        }
    };
</script>

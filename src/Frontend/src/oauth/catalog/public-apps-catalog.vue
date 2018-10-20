<template>
    <loading-cover :loading="apps === undefined">
        <div class="container">
            <h1>SUPLA Apps</h1>
            <public-apps-catalog-not-registered-message></public-apps-catalog-not-registered-message>
        </div>
    </loading-cover>
</template>

<script>
    import PublicAppsCatalogNotRegisteredMessage from "./public-apps-catalog-not-registered-message";

    export default {
        components: {PublicAppsCatalogNotRegisteredMessage},
        data() {
            return {
                apps: undefined,
            };
        },
        computed: {},
        mounted() {
            this.$http.get('public-oauth-apps')
                .then(({body: apps}) => this.apps = apps)
                .catch((response) => this.apps = response.status);
        }
    };
</script>

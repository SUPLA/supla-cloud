<template>
    <square-link class="clearfix pointer yellow public-app">
        <router-link :to="linkSpec">
            <img class="app-image"
                :src="'https://api.thecatapi.com/v1/images/search?format=src&size=full&' + app.id">
            <h3>{{ app.name }}</h3>
            <p>{{ app.description }}</p>
        </router-link>
    </square-link>
</template>

<script>

    export default {
        props: ['app', 'noLink'],
        computed: {
            linkSpec() {
                return this.noLink ? {} : {name: 'publicApps', params: {id: this.app.id}};
            }
        }
    };
</script>

<style lang="scss">
    .public-app {
        .app-image {
            max-width: 100px;
            max-height: 60px;
            float: right;
            margin-left: 3px;
            margin-bottom: 3px;
        }
        h3 {
            white-space: normal !important;
        }
    }
</style>

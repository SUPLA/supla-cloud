<template>
    <square-link class="clearfix pointer blue public-app">
        <router-link :to="linkSpec">
            <img class="app-icon" alt="Logo"
                :src="`https://www.supla.org/assets/img/AD/${app.id}.webp`">
            <h3>{{ app.name }}</h3>
            <p>
                <i18n-text :text="app.description"></i18n-text>
            </p>
        </router-link>
    </square-link>
</template>

<script>

    import I18nText from "./i18n-text.vue";

    export default {
        components: {I18nText},
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
        .app-icon {
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

<template>
    <div>
        <select v-model="locale"
            @change="updateLocale()">
            <option value="en">English</option>
            <option value="pl">Polski</option>
            <option value="ru">Русский</option>
            <option value="de">Deutsch</option>
            <option value="it">Italiano</option>
            <option value="pt">Português</option>
            <option value="es">Español</option>
            <option value="fr">Français</option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";

    export default {
        data() {
            return {
                locale: ''
            };
        },
        mounted() {
            this.locale = Vue.config.lang;
        },
        methods: {
            updateLocale() {
                this.$http.put('ajax/lngset/' + this.locale).then(() => window.location.reload());
            }
        }
    };
</script>

<style scoped>
    select {
        padding: 0px 7px;
        margin: 0;
        border-radius: 3px;
        background: rgba(255, 255, 255, 0);
        outline: none;
        display: inline-block;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        cursor: pointer;
        color: #000;
        border: solid 1px rgba(0, 2, 4, 0.15);
        width: 150px;
        height: 32px !important;
        line-height: 32px;
    }

    select::-ms-expand {
        display: none;
    }
</style>

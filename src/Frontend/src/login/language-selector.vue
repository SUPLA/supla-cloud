<template>
    <div>
        <select v-model="locale"
            @change="updateLocale()">
            <option value="en">English</option>
            <option value="pl">Polski</option>
            <option value="cs">Čeština</option>
            <option value="lt">Lietuvių</option>
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
            this.locale = Vue.config.external.locale;
        },
        methods: {
            updateLocale() {
                this.safeInsertGetParamToUrl('lang', this.locale);
            },
            // idea based on https://stackoverflow.com/a/487049/878514
            safeInsertGetParamToUrl(key, value) {
                key = encodeURI(key);
                value = encodeURI(value);
                const kvp = document.location.search.substr(1).split('&');
                let i = kvp.length;
                let x;
                while (i--) {
                    x = kvp[i].split('=');
                    if (x[0] == key) {
                        x[1] = value;
                        kvp[i] = x.join('=');
                        break;
                    }
                }
                if (i < 0) {
                    kvp[kvp.length] = [key, value].join('=');
                }
                document.location.search = kvp.join('&');
            }
        }
    };
</script>

<style scoped
    lang="scss">
    @import "../styles/variables";

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
        color: $supla-black;
        border: solid 1px rgba(0, 2, 4, 0.15);
        width: 150px;
        height: 32px !important;
        line-height: 32px;
        &::-ms-expand {
            display: none;
        }
        option {
            color: $supla-black !important;
        }
    }
</style>

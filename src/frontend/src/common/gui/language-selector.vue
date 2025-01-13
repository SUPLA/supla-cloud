<template>
    <div>
        <select v-model="locale"
            @change="updateLocale()">
            <option
                v-for="locale in locales"
                :key="locale.value"
                :value="locale.value">{{ locale.text }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import {setGuiLocale} from "@/locale";

    export default {
        data() {
            return {
                locale: ''
            };
        },
        computed: {
            locales() {
                return Vue.config.availableLanguages;
            }
        },
        mounted() {
            this.locale = this.$i18n.locale;
        },
        methods: {
            updateLocale() {
                setGuiLocale(this.locale);
            }
        },
        watch: {
            '$i18n.locale'() {
                this.locale = this.$i18n.locale;
            }
        }
    };
</script>

<style scoped
    lang="scss">
    @import "../../styles/variables";

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

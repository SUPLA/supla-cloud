<template>
    <span class="currency-picker">
        <select data-live-search="true"
            :data-live-search-placeholder="$t('Search')"
            :data-none-selected-text="$t('choose a currency')"
            :data-none-results-text="$t('No results match {0}')"
            data-width="100%"
            v-model="chosenCurrency"
            @change="$emit('input', chosenCurrency)"
            ref="dropdown">
            <option v-for="code in currencyCodeList"
                :value="code">
                {{ code }}
            </option>
        </select>
    </span>
</template>

<script type="text/babel">
    import Vue from "vue";
    import "bootstrap-select";
    import "bootstrap-select/dist/css/bootstrap-select.css";
    import CurrencyCodes from "currency-codes";

    export default {
        props: ['value'],
        data() {
            return {
                chosenCurrency: undefined
            };
        },
        mounted() {
            this.chosenCurrency = this.value;
            Vue.nextTick(() => $(this.$refs.dropdown).selectpicker());
        },
        computed: {
            currencyCodeList() {
                return CurrencyCodes.codes();
            }
        }
    };
</script>

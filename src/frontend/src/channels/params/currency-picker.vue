<template>
    <span class="currency-picker">
        <SelectForSubjects
            class="schedules-dropdown"
            do-not-hide-selected
            :options="currencyCodesList"
            :option-groups="groups"
            choose-prompt-i18n="choose the currency"
            :max-options="200"
            v-model="chosenCurrency"/>
    </span>
</template>

<script>
    import SelectForSubjects from "@/devices/select-for-subjects.vue";

    export default {
        components: {SelectForSubjects},
        props: ['value'],
        data() {
            return {
                currencyCodes: [
                    'PLN', 'EUR', 'USD', 'GBP',
                    'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD',
                    'BND', 'BOB', 'BOV', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHE', 'CHF', 'CHW', 'CLF', 'CLP', 'CNY',
                    'COP', 'COU', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB', 'FJD', 'FKP', 'GEL',
                    'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK',
                    'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL',
                    'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MUR', 'MVR', 'MWK', 'MXN', 'MXV', 'MYR', 'MZN', 'NAD',
                    'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF',
                    'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD', 'SHP', 'SLL', 'SOS', 'SRD', 'SSP', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS',
                    'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS', 'UAH', 'UGX', 'USN', 'UYI', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST',
                    'XAF', 'XCD', 'XDR', 'XOF', 'XPF', 'XSU', 'XUA', 'YER', 'ZAR', 'ZMW', 'ZWL'],
                commonCurrencyCodes: ['PLN', 'EUR', 'USD', 'GBP'],
                groups: [
                    {labelI18n: 'Common currencies', id: 'common'}, // i18n
                    {labelI18n: 'Other', id: 'other'}, // i18n
                ]
            };
        },
        computed: {
            chosenCurrency: {
                get() {
                    return this.value ? {id: this.value, caption: this.value} : undefined;
                },
                set(value) {
                    this.$emit('input', value.id);
                }
            },
            currencyCodesList() {
                return this.currencyCodes.map(code => ({
                        id: code,
                        caption: code,
                        group: this.commonCurrencyCodes.includes(code) ? 'common' : 'other'
                    })
                );
            }
        },
    };
</script>

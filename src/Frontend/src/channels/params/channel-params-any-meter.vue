<template>
    <dl>
        <dd>{{ $t('Price per') }} {{ unit }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.0001"
                    min="0"
                    max="1000"
                    class="form-control text-center"
                    v-model="pricePerUnit">
            </span>
        </dt>

        <dd>{{ $t('Currency') }}</dd>
        <dt>
            <currency-picker v-model="currency"></currency-picker>
        </dt>
    </dl>
</template>

<script>
    import CurrencyPicker from "./currency-picker";

    export default {
        components: {CurrencyPicker},
        props: ['channel', 'unit'],
        computed: {
            pricePerUnit: {
                set(value) {
                    this.channel.param2 = value * 10000;
                    this.$emit('change');
                },
                get() {
                    return this.channel.param2 / 10000;
                }
            },
            currency: {
                set(value) {
                    this.channel.textParam1 = value;
                    this.$emit('change');
                },
                get() {
                    return this.channel.textParam1;
                }
            }
        },
    };
</script>

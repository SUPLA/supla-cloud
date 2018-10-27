<template>
    <dl>
        <dd>{{ $t('Price per') }} {{ _unit }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.01"
                    min="0"
                    max="1000"
                    class="form-control text-center"
                    v-model="param2">
            </span>
        </dt>

        <dd>{{ $t('Currency') }}</dd>
        <dt>
            <currency-picker v-model="param4"></currency-picker>
        </dt>
    </dl>
</template>

<script>
    import CurrencyPicker from "./currency-picker";

    export default {
        components: {CurrencyPicker},
        props: ['channel', 'unit'],
        computed: {
            param2: {
                set(value) {
                    this.channel.param2 = value * 100;
                    this.$emit('change');
                },
                get() {
                    return this.channel.param2 / 100;
                }
            },
            param4: {
                set(value) {
                    this.channel.param4 = value;
                    this.$emit('change');
                },
                get() {
                    return this.channel.param4;
                }
            },
            _unit: {
                get() {
                    return this.unit ? this.unit : 'm³';
                }
            },
        },
    };
</script>

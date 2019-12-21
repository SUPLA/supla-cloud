<template>
    <dl>
        <dd>{{ $t('Price per') }} {{ unit }}</dd>
        <dt>
            <input type="number"
                step="0.0001"
                min="0"
                max="1000"
                class="form-control text-center"
                v-model="channel.params.pricePerUnit"
                @input="$emit('change')">
        </dt>
        <dd>{{ $t('Currency') }}</dd>
        <dt>
            <currency-picker v-model="channel.params.currency"
                @input="$emit('change')"></currency-picker>
        </dt>
    </dl>
</template>

<script>
    import CurrencyPicker from "./currency-picker";

    export default {
        components: {CurrencyPicker},
        props: ['channel', 'unit'],
    };
</script>

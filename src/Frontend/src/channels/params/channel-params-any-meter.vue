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
                    v-model="channel.config.pricePerUnit"
                    @change="$emit('change')">
            </span>
        </dt>

        <dd>{{ $t('Currency') }}</dd>
        <dt>
            <currency-picker v-model="channel.config.currency"></currency-picker>
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

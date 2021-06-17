<template>
    <div>
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

        <channel-params-related-channel
            :channel="channel"
            label-i18n="Associated measured channel"
            channel-filter="function=POWERSWITCH,LIGHTSWITCH"
            param-no="4"
            @change="$emit('change')"></channel-params-related-channel>
    </div>
</template>

<script>
    import CurrencyPicker from "./currency-picker";

    export default {
        components: {CurrencyPicker},
        props: ['channel', 'unit'],
    };
</script>

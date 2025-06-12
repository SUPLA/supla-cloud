<template>
    <div>
        <div v-if="channel.config.virtualChannelConfig.type === 'ENERGY_PRICE_FORECAST'">
            <dl>
                <dd>{{ $t('Data source type') }}</dd>
                <dt>{{ $t('Energy prices') }}</dt>
            </dl>
            <div class="alert alert-info small my-3">{{ $t('virtualChannelTypeInfoLong_ENERGY_PRICE_FORECAST') }}</div>
            <dl>
                <dd>{{ $t('Attribute') }}</dd>
                <dt>{{ $t(`energyPriceForecast_field_${channel.config.virtualChannelConfig.energyField}`) }}</dt>
            </dl>
        </div>
    </div>
</template>

<script setup>
    defineProps({channel: Object});
</script>

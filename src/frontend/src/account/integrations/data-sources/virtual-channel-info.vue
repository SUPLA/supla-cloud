<template>
    <div>
        <VirtualChannelInfoOpenWeather v-if="channel.config.virtualChannelConfig.type === 'OPEN_WEATHER'" :channel="channel"/>
        <VirtualChannelInfoEnergyPriceForecast v-if="channel.config.virtualChannelConfig.type === 'ENERGY_PRICE_FORECAST'"
            :channel="channel"/>
    </div>
</template>

<script setup>
    import VirtualChannelInfoOpenWeather from "@/account/integrations/data-sources/virtual-channel-info-open-weather.vue";
    import VirtualChannelInfoEnergyPriceForecast from "@/account/integrations/data-sources/virtual-channel-info-energy-price-forecast.vue";

    defineProps({channel: Object});
</script>

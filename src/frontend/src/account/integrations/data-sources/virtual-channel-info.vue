<template>
    <div>
        <VirtualChannelInfoOpenWeather v-if="channel.config.virtualChannelConfig.type === 'OPEN_WEATHER'"
            :channel="channel"/>
    </div>
</template>

<script setup>
    import VirtualChannelInfoOpenWeather from "@/account/integrations/data-sources/virtual-channel-info-open-weather.vue";

    defineProps({channel: Object});
</script>

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
                <dt>{{ $t(field.label) }} ({{ field.unit }})</dt>
            </dl>
        </div>
    </div>
</template>

<script setup>
    import {computed} from "vue";
    import {useEnergyPricesForecastStore} from "@/stores/energy-prices-forecast-store";

    const props = defineProps({channel: Object});

    const store = useEnergyPricesForecastStore();

    const field = computed(() => store.availableEnergyPrices?.find((field) => field.id === props.channel.config.virtualChannelConfig.energyField))
</script>

<template>
    <div>
        <div v-if="channel.config.virtualChannelConfig.type === 'OPEN_WEATHER'">
            <dl>
                <dd>{{ $t('Data source type') }}</dd>
                <dt>{{ $t('Open Weather Data') }}</dt>
            </dl>
            <div class="alert alert-info small my-3">{{ $t('virtualChannelTypeInfoLong_OPEN_WEATHER') }}</div>
            <dl>
                <dd>{{ $t('City') }}</dd>
                <dt>{{ city?.displayName || '...' }}</dt>
            </dl>
            <dl>
                <dd>{{ $t('Weather attribute') }}</dd>
                <dt>{{ $t(`openWeatherAttribute_field_${field.id}`) }} ({{ field.unit }})</dt>
            </dl>
        </div>
    </div>
</template>

<script setup>
    import {useOpenWeatherStore} from "@/stores/open-weather-store";
    import {computed} from "vue";

    const props = defineProps({channel: Object});

    const openWeatherStore = useOpenWeatherStore();

    const city = computed(() => openWeatherStore.availableCities?.find((city) => city.id === props.channel.config.virtualChannelConfig.cityId))
    const field = computed(() => openWeatherStore.availableWeatherFields?.find((field) => field.id === props.channel.config.virtualChannelConfig.weatherField))
</script>

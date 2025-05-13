<template>
    <div>
        <div v-if="channel.config.virtualChannelType === 'OPEN_WEATHER'">
            <dl>
                <dd>{{ $t('Data source type') }}</dd>
                <dt>{{ $t('Open Weather Data') }}</dt>
            </dl>
            <div class="alert alert-info small my-3">{{ $t('virtualChannelTypeInfoLong_OPEN_WEATHER') }}</div>
            <dl>
                <dd>{{ $t('City') }}</dd>
                <dt>
                    <SelectForSubjects
                        :none-option="false"
                        :options="availableCities"
                        :caption="(c) => c.name"
                        :search-text="(c) => c.name"
                        choose-prompt-i18n="choose the city"
                        v-model="city"/>
                </dt>
            </dl>
            <dl>
                <dd>{{ $t('Weather attribute') }}</dd>
                <dt>
                    <SelectForSubjects
                        :none-option="false"
                        :options="availableWeatherData"
                        :caption="(c) => `${$t(c.label)} (${c.unit})`"
                        :search-text="(c) => $t(c.label)"
                        choose-prompt-i18n="choose the attribute"
                        v-model="field"/>
                </dt>
            </dl>
        </div>
    </div>
</template>

<script setup>
    import {useSuplaApi} from "@/api/use-supla-api";
    import SelectForSubjects from "@/devices/select-for-subjects.vue";
    import {computed, ref} from "vue";

    const props = defineProps({
        channel: Object,
    });

    const emit = defineEmits(['change']);

    const field = ref();

    const {data: availableCities} = useSuplaApi(`/integrations/openweather/cities`).json();

    const city = computed({
        get: () => availableCities.value?.find(c => c.id === props.channel.config.cityId),
        set(city) {
            props.channel.config.cityId = city.id;
            emit('change');
        }
    })

    const availableWeatherData = [
        {label: 'Temperature', id: 'temp', unit: '°C'}, // i18n
        {label: 'Perceived temperature', id: 'feelsLike', unit: '°C'}, // i18n
        {label: 'Pressure', id: 'pressure', unit: 'hPa'}, // i18n
        {label: 'Humidity', id: 'humidity', unit: '%'}, // i18n
        {label: 'Visibility', id: 'visibility', unit: 'm'}, // i18n
        {label: 'Wind Speed', id: 'windSpeed', unit: 'm/s'}, // i18n
        {label: 'Clouds', id: 'clouds', unit: '%'}, // i18n
        {label: 'Rain', id: 'rainMmh', unit: 'mm/h'}, // i18n
        {label: 'Snow', id: 'snowMmh', unit: 'mm/h'}, // i18n
        {label: 'Air CO', id: 'airCo', unit: 'µg/m³'}, // i18n
        {label: 'Air NO', id: 'airNo', unit: 'µg/m³'}, // i18n
        {label: 'Air NO2', id: 'airNo2', unit: 'µg/m³'}, // i18n
        {label: 'Air O3', id: 'airO3', unit: 'µg/m³'}, // i18n
        {label: 'Air PM10', id: 'airPm10', unit: 'µg/m³'}, // i18n
        {label: 'Air PM2.5', id: 'airPm25', unit: 'µg/m³'}, // i18n
    ];
</script>


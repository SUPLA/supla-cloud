import {defineStore} from "pinia";
import {useSuplaApi} from "@/api/use-supla-api";
import {ref} from "vue";

export const useOpenWeatherStore = defineStore('openWeather', () => {
    const {data: availableCities} = useSuplaApi(`integrations/openweather/cities`).json();

    const availableWeatherFields = ref([
        {label: 'Temperature', id: 'temp', unit: '°C'}, // i18n
        {label: 'Humidity', id: 'humidity', unit: '%'}, // i18n
        {label: 'Temperature and humidity', id: 'tempHumidity', unit: '°C / %'}, // i18n
        {label: 'Perceived temperature', id: 'feelsLike', unit: '°C'}, // i18n
        {label: 'Pressure', id: 'pressure', unit: 'hPa'}, // i18n
        {label: 'Visibility', id: 'visibility', unit: 'm'}, // i18n
        {label: 'Wind Speed', id: 'windSpeed', unit: 'm/s'}, // i18n
        {label: 'Wind Gust', id: 'windGust', unit: 'm/s'}, // i18n
        {label: 'Clouds', id: 'clouds', unit: '%'}, // i18n
        {label: 'Rain', id: 'rainMmh', unit: 'mm/h'}, // i18n
        {label: 'Snow', id: 'snowMmh', unit: 'mm/h'}, // i18n
        {label: 'Air CO', id: 'airCo', unit: 'µg/m³'}, // i18n
        {label: 'Air NO', id: 'airNo', unit: 'µg/m³'}, // i18n
        {label: 'Air NO₂', id: 'airNo2', unit: 'µg/m³'}, // i18n
        {label: 'Air O₃', id: 'airO3', unit: 'µg/m³'}, // i18n
        {label: 'Air PM10', id: 'airPm10', unit: 'µg/m³'}, // i18n
        {label: 'Air PM2.5', id: 'airPm25', unit: 'µg/m³'}, // i18n
    ]);

    const $reset = () => {
    };

    return {availableCities, availableWeatherFields, $reset};
})

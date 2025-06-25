import {defineStore} from "pinia";
import {useSuplaApi} from "@/api/use-supla-api";
import {ref} from "vue";

export const useOpenWeatherStore = defineStore('openWeather', () => {
    const {data: availableCities} = useSuplaApi(`integrations/openweather/cities`).json();

    const availableWeatherFields = ref([
        {id: 'temp', unit: '°C'},
        {id: 'humidity', unit: '%'},
        {id: 'tempHumidity', unit: '°C / %'},
        {id: 'feelsLike', unit: '°C'},
        {id: 'pressure', unit: 'hPa'},
        {id: 'visibility', unit: 'm'},
        {id: 'windSpeed', unit: 'm/s'},
        {id: 'windGust', unit: 'm/s'},
        {id: 'clouds', unit: '%'},
        {id: 'rainMmh', unit: 'mm/h'},
        {id: 'snowMmh', unit: 'mm/h'},
        {id: 'airCo', unit: 'µg/m³'},
        {id: 'airNo', unit: 'µg/m³'},
        {id: 'airNo2', unit: 'µg/m³'},
        {id: 'airO3', unit: 'µg/m³'},
        {id: 'airPm10', unit: 'µg/m³'},
        {id: 'airPm25', unit: 'µg/m³'},
    ]);

    const $reset = () => {
    };

    return {availableCities, availableWeatherFields, $reset};
})

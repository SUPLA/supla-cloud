import {defineStore} from 'pinia';
import {useSuplaApi} from '@/api/use-supla-api';
import {computed, ref} from 'vue';

// i18n: ['openWeatherAttribute_field_temp', 'openWeatherAttribute_field_humidity', 'openWeatherAttribute_field_tempHumidity']
// i18n: ['openWeatherAttribute_field_feelsLike', 'openWeatherAttribute_field_pressure', 'openWeatherAttribute_field_visibility']
// i18n: ['openWeatherAttribute_field_windSpeed', 'openWeatherAttribute_field_windGust', 'openWeatherAttribute_field_rainMmh']
// i18n: ['openWeatherAttribute_field_snowMmh', 'openWeatherAttribute_field_airCo', 'openWeatherAttribute_field_airNo']
// i18n: ['openWeatherAttribute_field_airNo2', 'openWeatherAttribute_field_airO3', 'openWeatherAttribute_field_airPm10']
// i18n: ['openWeatherAttribute_field_airPm25']

export const useOpenWeatherStore = defineStore('openWeather', () => {
  const {data: availableCitiesFromApi} = useSuplaApi(`integrations/openweather/cities`).json();

  const availableWeatherFields = ref([
    {id: 'temp', unit: '°C'},
    {id: 'humidity', unit: '%'},
    {id: 'tempHumidity', unit: '°C / %'},
    {id: 'feelsLike', unit: '°C'},
    {id: 'pressure', unit: 'hPa'},
    {id: 'visibility', unit: 'm'},
    {id: 'windSpeed', unit: 'm/s'},
    {id: 'windGust', unit: 'm/s'},
    // {id: 'clouds', unit: '%'},
    {id: 'rainMmh', unit: 'mm/h'},
    {id: 'snowMmh', unit: 'mm/h'},
    {id: 'airCo', unit: 'µg/m³'},
    {id: 'airNo', unit: 'µg/m³'},
    {id: 'airNo2', unit: 'µg/m³'},
    {id: 'airO3', unit: 'µg/m³'},
    {id: 'airPm10', unit: 'µg/m³'},
    {id: 'airPm25', unit: 'µg/m³'},
  ]);

  const $reset = () => {};

  const availableCities = computed(() =>
    (availableCitiesFromApi.value || []).map((city) => ({
      ...city,
      displayName: city.county && city.name !== city.county ? `${city.name} (${city.county})` : city.name,
    }))
  );

  return {availableCities, availableWeatherFields, $reset};
});

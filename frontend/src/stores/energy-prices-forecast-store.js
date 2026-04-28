import {defineStore} from 'pinia';
import {useSuplaApi} from '@/api/use-supla-api';

export const useEnergyPricesForecastStore = defineStore('energyPricesForecast', () => {
  const {data: availableParameters} = useSuplaApi(`integrations/energy-price-forecast/parameters`).json();

  const $reset = () => {};

  return {availableParameters, $reset};
});

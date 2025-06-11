import {defineStore} from "pinia";
import {ref} from "vue";
import {useFrontendConfigStore} from "@/stores/frontend-config-store";

export const useEnergyPricesForecastStore = defineStore('energyPricesForecast', () => {
    const prices = [
        {label: 'PSE: Market Price of Electrical Energy in Poland (RCE)', id: 'rce', unit: 'PLN'}, // i18n
    ];
    if (useFrontendConfigStore().config.actAsBrokerCloud) {
        prices.push({label: 'TGE: RDN Indicator - fixing I', id: 'fixing1', unit: 'PLN'});
        prices.push({label: 'TGE: RDN Indicator - fixing II', id: 'fixing2', unit: 'PLN'});
    }

    const availableEnergyPrices = ref(prices);

    const $reset = () => {
    };

    return {availableEnergyPrices, $reset};
})

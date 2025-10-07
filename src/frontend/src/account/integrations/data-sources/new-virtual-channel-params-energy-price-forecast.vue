<template>
    <div>
        <div>
            <div class="alert alert-info small my-3">{{ $t('virtualChannelTypeInfoLong_ENERGY_PRICE_FORECAST') }}</div>
            <dl>
                <dt>{{ $t('Energy attribute') }}</dt>
                <dd>
                    <div class="d-flex flex-wrap">
                        <!-- i18n: ['energyPriceForecast_field_rce', 'energyPriceForecast_field_fixing1', 'energyPriceForecast_field_fixing2'] -->
                        <button
                            v-for="energyField in energyPricesForecastStore.availableParameters"
                            :key="energyField"
                            type="button"
                            :class="['btn flex-grow-1 m-1', energyField === field ? 'btn-green' : 'btn-default']"
                            @click="field = energyField">
                            {{ $t(`energyPriceForecast_field_${energyField}`) }}
                        </button>
                    </div>
                </dd>
            </dl>
        </div>
    </div>
</template>

<script setup>
  import {computed} from "vue";
  import {useEnergyPricesForecastStore} from "@/stores/energy-prices-forecast-store";

  const props = defineProps({value: Object});
    const emit = defineEmits(['input']);

    const energyPricesForecastStore = useEnergyPricesForecastStore();

    const field = computed({
        get: () => props.value.energyField,
        set: (field) => emitConfig({...props.value, energyField: field}),
    });

    function emitConfig(config) {
        config.ready = !!config.energyField;
        emit('input', config)
    }
</script>


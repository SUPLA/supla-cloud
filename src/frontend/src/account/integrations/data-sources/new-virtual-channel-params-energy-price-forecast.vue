<template>
    <div>
        <div>
            <div class="alert alert-info small my-3">{{ $t('virtualChannelTypeInfoLong_ENERGY_PRICE_FORECAST') }}</div>
            <dl>
                <dt>{{ $t('Energy attribute') }}</dt>
                <dd>
                    <div class="d-flex flex-wrap">
                        <button
                            v-for="energyField in energyPricesForecastStore.availableEnergyPrices"
                            :key="energyField.id"
                            type="button"
                            :class="['btn flex-grow-1 m-1', energyField.id === field?.id ? 'btn-green' : 'btn-default']"
                            @click="field = energyField">
                            {{ $t(energyField.label) }} ({{ energyField.unit }})
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
        get: () => energyPricesForecastStore.availableEnergyPrices.find(c => c.id === props.value.energyField),
        set: (fieldData) => emitConfig({...props.value, energyField: fieldData.id}),
    });

    function emitConfig(config) {
        config.ready = !!config.energyField;
        emit('input', config)
    }
</script>


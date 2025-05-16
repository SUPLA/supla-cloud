<template>
    <div>
        <div>
            <div class="alert alert-info small my-3">{{ $t('virtualChannelTypeInfoLong_OPEN_WEATHER') }}</div>
            <dl>
                <dt>{{ $t('City') }}</dt>
                <dd>
                    <SelectForSubjects
                        :none-option="false"
                        :options="openWeatherStore.availableCities"
                        :caption="(c) => c.name"
                        :search-text="(c) => c.name"
                        choose-prompt-i18n="choose the city"
                        v-model="city"/>
                </dd>
            </dl>
            <dl>
                <dt>{{ $t('Weather attribute') }}</dt>
                <dd>
                    <SelectForSubjects
                        :none-option="false"
                        :options="openWeatherStore.availableWeatherFields"
                        :caption="(c) => `${$t(c.label)} (${c.unit})`"
                        :search-text="(c) => $t(c.label)"
                        choose-prompt-i18n="choose the attribute"
                        v-model="field"/>
                </dd>
            </dl>
        </div>
    </div>
</template>

<script setup>
    import SelectForSubjects from "@/devices/select-for-subjects.vue";
    import {computed} from "vue";
    import {useOpenWeatherStore} from "@/stores/open-weather-store";

    const props = defineProps({value: Object});
    const emit = defineEmits(['input']);

    const openWeatherStore = useOpenWeatherStore();

    const city = computed({
        get: () => openWeatherStore.availableCities?.find(c => c.id === props.value.cityId),
        set: (city) => emitConfig({...props.value, cityId: city.id}),
    });

    const field = computed({
        get: () => openWeatherStore.availableWeatherFields.find(c => c.id === props.value.weatherField),
        set: (fieldData) => emitConfig({...props.value, weatherField: fieldData.id}),
    });

    function emitConfig(config) {
        config.ready = config.cityId && config.weatherField;
        emit('input', config)
    }
</script>


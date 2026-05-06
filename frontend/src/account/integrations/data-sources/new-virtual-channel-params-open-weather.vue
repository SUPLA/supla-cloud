<template>
  <div>
    <div>
      <div class="alert alert-info small my-3">{{ $t('virtualChannelTypeInfoLong_OPEN_WEATHER') }}</div>
      <dl>
        <dt>{{ $t('City') }}</dt>
        <dd>
          <SelectForSubjects
            v-model="city"
            :none-option="false"
            :options="openWeatherStore.availableCities"
            :caption="(c) => c.displayName"
            :search-text="(c) => c.displayName"
            choose-prompt-i18n="choose the city"
          />
        </dd>
      </dl>
      <dl>
        <dt>{{ $t('Weather attribute') }}</dt>
        <dd>
          <div class="d-flex flex-wrap">
            <button
              v-for="weatherField in openWeatherStore.availableWeatherFields"
              :key="weatherField.id"
              type="button"
              :class="['btn flex-grow-1 m-1', weatherField.id === field?.id ? 'btn-green' : 'btn-default']"
              @click="field = weatherField"
            >
              {{ $t(`openWeatherAttribute_field_${weatherField.id}`) }} ({{ weatherField.unit }})
            </button>
          </div>
        </dd>
      </dl>
    </div>
  </div>
</template>

<script setup>
  import SelectForSubjects from '@/devices/select-for-subjects.vue';
  import {computed} from 'vue';
  import {useOpenWeatherStore} from '@/stores/open-weather-store';

  const props = defineProps({value: Object});
  const emit = defineEmits(['input']);

  const openWeatherStore = useOpenWeatherStore();

  const city = computed({
    get: () => openWeatherStore.availableCities?.find((c) => c.id === props.value.cityId),
    set: (city) => emitConfig({...props.value, cityId: city.id}),
  });

  const field = computed({
    get: () => openWeatherStore.availableWeatherFields.find((c) => c.id === props.value.weatherField),
    set: (fieldData) => emitConfig({...props.value, weatherField: fieldData.id}),
  });

  function emitConfig(config) {
    config.ready = config.cityId && config.weatherField;
    emit('input', config);
  }
</script>

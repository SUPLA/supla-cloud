<template>
  <div>
    <div class="form-group">
      <label>{{ $t('Screen brightness') }}</label>
      <div>
        <label class="checkbox2 checkbox2-grey">
          <input v-model="newConfig.screenBrightness.auto" type="checkbox" @input="onScreenBrightnessAutoChange()" />
          {{ $t('Automatic') }}
        </label>
      </div>
    </div>
    <div v-if="newConfig.screenBrightness.auto" class="form-group">
      <label>{{ $t('Brightness adjustment for automatic mode') }}</label>
      <div class="mt-4 mb-6">
        <FormSlider
          v-model="newConfig.screenBrightness.level"
          :min="-100"
          :max="100"
          :interval="10"
          :tooltip-formatter="(v) => (v === 0 ? $t('default') : (v > 0 ? '+' + v : v) + '%')"
          :marks="{0: {label: ''}}"
        >
          <template #label>
            <div class="vue-slider-mark-label mark-on-top">
              <fa icon="circle-half-stroke" />
            </div>
          </template>
        </FormSlider>
      </div>
    </div>
    <div v-else class="form-group">
      <label>{{ $t('Brightness level') }}</label>
      <div class="mt-3 mb-6">
        <FormSlider
          v-model="newConfig.screenBrightness.level"
          :data="[1, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55, 60, 65, 70, 75, 80, 85, 90, 95, 100]"
          :tooltip-formatter="(v) => `${v}%`"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
  import {deepCopy} from '@/common/utils';
  import {useDeviceSettingsForm} from '@/devices/details/device-details-helpers';
  import FormSlider from '@/common/form/FormSlider.vue';

  const props = defineProps({device: Object});
  const emit = defineEmits(['change']);

  const {newConfig} = useDeviceSettingsForm(props.device.id, emit, (device) => ({
    screenBrightness: deepCopy(device.config.screenBrightness) || {},
  }));

  const onScreenBrightnessAutoChange = () => (newConfig.value.screenBrightness.level = newConfig.value.screenBrightness.auto ? 50 : 0);
</script>

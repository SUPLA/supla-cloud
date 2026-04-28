<template>
  <div>
    <div v-if="newConfig.statusLed" class="form-group">
      <label for="statusLed">{{ $t('Status LED') }}</label>
      <select id="statusLed" v-model="newConfig.statusLed" class="form-control">
        <option value="OFF_WHEN_CONNECTED">{{ $t('statusLed_OFF_WHEN_CONNECTED') }}</option>
        <option value="ALWAYS_OFF">{{ $t('statusLed_ALWAYS_OFF') }}</option>
        <option value="ON_WHEN_CONNECTED">{{ $t('statusLed_ON_WHEN_CONNECTED') }}</option>
      </select>
    </div>
    <div v-if="powerStatusLedBoolean !== undefined" class="form-group">
      <label class="checkbox2 checkbox2-grey">
        <input v-model="powerStatusLedBoolean" type="checkbox" />
        {{ $t('Enable power status LED') }}
      </label>
    </div>
  </div>
</template>

<script setup>
  import {computed} from 'vue';
  import {useDeviceSettingsForm} from '@/devices/details/device-details-helpers';

  const props = defineProps({device: Object});

  const emit = defineEmits(['change']);

  const {newConfig} = useDeviceSettingsForm(props.device.id, emit, (device) => ({
    powerStatusLed: device.config.powerStatusLed,
    statusLed: device.config.statusLed,
  }));

  const powerStatusLedBoolean = computed({
    get() {
      return newConfig.value.powerStatusLed === undefined ? undefined : newConfig.value.powerStatusLed === 'ENABLED';
    },
    set(value) {
      newConfig.value.powerStatusLed = value ? 'ENABLED' : 'DISABLED';
    },
  });
</script>

<template>
  <div>
    <DeviceDetailsTabSettingsHomeScreen :device="device" @change="emit('change', $event)" />
    <div v-if="newConfig.userInterface !== undefined" class="form-group">
      <label>{{ $t('Lock type') }}</label>
      <SimpleDropdown v-slot="{value}" v-model="localUILockMode" :options="['UNLOCKED', 'FULL', 'TEMPERATURE']">
        {{ $t(`localUILock_${value}`) }}
      </SimpleDropdown>
      <div v-if="newConfig.userInterface.disabled === 'partial'" class="mt-3">
        <label>{{ $t('Temperatures that can be set from local UI') }}</label>
        <div class="row">
          <div class="col-sm-6">
            <div class="form-group">
              <div>{{ $t('Minimum') }}</div>
              <input
                v-model="newConfig.userInterface.minAllowedTemperatureSetpointFromLocalUI"
                type="number"
                class="form-control"
                step="0.1"
                :max="newConfig.userInterface.maxAllowedTemperatureSetpointFromLocalUI || maxUiTemperature"
                :min="minUiTemperature"
                :placeholder="minUiTemperature"
              />
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <div>{{ $t('Maximum') }}</div>
              <input
                v-model="newConfig.userInterface.maxAllowedTemperatureSetpointFromLocalUI"
                type="number"
                class="form-control"
                step="0.1"
                :min="newConfig.userInterface.minAllowedTemperatureSetpointFromLocalUI || minUiTemperature"
                :max="maxUiTemperature"
                :placeholder="maxUiTemperature"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
    <DeviceDetailsTabSettingsScreenBrightness :device="device" @change="emit('change', $event)" />
    <div v-if="newConfig.buttonVolume !== undefined" class="form-group">
      <label>{{ $t('Button volume') }}</label>
      <div class="mt-3 mb-6">
        <FormSlider v-model="newConfig.buttonVolume" :min="0" :max="100" />
      </div>
    </div>
  </div>
</template>

<script setup>
  import {computed} from 'vue';
  import {deepCopy} from '@/common/utils';
  import SimpleDropdown from '@/common/gui/simple-dropdown.vue';
  import {useDeviceSettingsForm} from '@/devices/details/device-details-helpers';
  import DeviceDetailsTabSettingsHomeScreen from '@/devices/details/device-details-tab-settings-home-screen.vue';
  import DeviceDetailsTabSettingsScreenBrightness from '@/devices/details/device-details-tab-settings-screen-brightness.vue';
  import FormSlider from '@/common/form/FormSlider.vue';

  const props = defineProps({device: Object});
  const emit = defineEmits(['change']);

  const {newConfig} = useDeviceSettingsForm(props.device.id, emit, (device) => ({
    userInterface: deepCopy(device.config.userInterface),
    buttonVolume: device.config.buttonVolume,
  }));

  const minUiTemperature = computed(() => props.device.config.userInterfaceConstraints?.minAllowedTemperatureSetpoint);
  const maxUiTemperature = computed(() => props.device.config.userInterfaceConstraints?.maxAllowedTemperatureSetpoint);

  const localUILockMode = computed({
    get() {
      if (newConfig.value.userInterface.disabled === 'partial') {
        return 'TEMPERATURE';
      }
      return newConfig.value.userInterface.disabled ? 'FULL' : 'UNLOCKED';
    },
    set(mode) {
      if (mode === 'FULL') {
        newConfig.value.userInterface.disabled = true;
      } else if (mode === 'TEMPERATURE') {
        newConfig.value.userInterface.disabled = 'partial';
      } else {
        newConfig.value.userInterface.disabled = false;
      }
    },
  });
</script>

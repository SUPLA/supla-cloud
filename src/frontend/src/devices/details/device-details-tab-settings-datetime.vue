<template>
  <div>
    <div v-if="newConfig.automaticTimeSync !== undefined" class="form-group">
      <label class="checkbox2 checkbox2-grey">
        <input v-model="newConfig.automaticTimeSync" type="checkbox" />
        {{ $t('Automatic time synchronization') }}
      </label>
    </div>
    <TransitionExpand>
      <div v-if="newConfig.automaticTimeSync === false" class="form-group">
        <DeviceSetTimeButton :device="device" class="mb-2" />
      </div>
    </TransitionExpand>
  </div>
</template>

<script setup>
  import {useDeviceSettingsForm} from '@/devices/details/device-details-helpers';
  import DeviceSetTimeButton from '@/devices/details/device-set-time-button.vue';
  import TransitionExpand from '@/common/gui/transition-expand.vue';

  const props = defineProps({device: Object});

  const emit = defineEmits(['change']);

  const {newConfig} = useDeviceSettingsForm(props.device.id, emit, (device) => ({
    automaticTimeSync: device.config.automaticTimeSync,
  }));
</script>

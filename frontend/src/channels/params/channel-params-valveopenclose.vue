<template>
  <div>
    <dl v-if="supportFloodSensors">
      <dd class="valign-top">{{ $t('Flood sensors') }}</dd>
      <dt>
        <div class="mb-3">
          <div v-for="(sensor, slot) in floodSensors" :key="slot" class="d-flex align-items-center bottom-border py-2">
            <div class="flex-grow-1">
              <h5 class="my-1">
                {{ sensor ? channelTitle(sensor) : $t('None') }}
                <small v-if="sensor" class="text-muted"> / {{ devicesStore.all[sensor.iodeviceId]?.name }}</small>
                <small v-if="floodSensorsIds[slot] && !validSensor(floodSensorsIds[slot])" class="text-warning" role="alert"
                  >{{ $t('Selected channel is unavailable or incompatible. Remove it and choose another channel.') }} (ID{{ floodSensorsIds[slot] }})</small
                >
              </h5>
            </div>
            <div class="pl-3">
              <a class="text-default" @click="handleRemoveSensor(slot)">
                <fa icon="trash" />
              </a>
            </div>
          </div>
        </div>
        <span class="small">{{ $t('Choose many') }}</span>
        <ChannelsDropdown
          :hide-none="true"
          :params="{skipIds: floodSensorsIds}"
          :destination="channel"
          reference-role="floodSensorChannelIds"
          :disabled="floodSensorsIds.length >= 20"
          @input="handleNewSensor"
        />
      </dt>
    </dl>
    <dl v-if="channel.config.closeValveOnFloodType">
      <dd class="valign-top">
        {{ $t('Auto-close on flood') }}
        <a @click="closeValveOnFloodTypeHelpShown = !closeValveOnFloodTypeHelpShown"><i class="pe-7s-help1"></i></a>
      </dd>
      <dt>
        <!-- i18n: ['closeValveOnFloodType_ALWAYS', 'closeValveOnFloodType_ON_CHANGE'] -->
        <SimpleDropdown v-slot="{value}" v-model="channel.config.closeValveOnFloodType" :options="['ALWAYS', 'ON_CHANGE']" @input="$emit('change')">
          {{ $t(`closeValveOnFloodType_${value}`) }}
        </SimpleDropdown>
        <transition-expand>
          <div v-if="closeValveOnFloodTypeHelpShown" class="well small text-muted p-2 mt-2 display-newlines">
            {{ $t('closeValveOnFloodType_help') }}
          </div>
        </transition-expand>
      </dt>
    </dl>
  </div>
</template>

<script setup>
  import {useDevicesStore} from '@/stores/devices-store';
  import {isChannelReferenceCandidate} from '@/channels/channel-reference-candidates';
  import {computed, ref} from 'vue';
  import {useChannelsStore} from '@/stores/channels-store';
  import ChannelsDropdown from '@/devices/channels-dropdown.vue';
  import {channelTitle} from '@/common/filters';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import SimpleDropdown from '@/common/gui/simple-dropdown.vue';

  const props = defineProps({channel: Object});
  const emit = defineEmits(['change']);

  const closeValveOnFloodTypeHelpShown = ref(false);

  const channelsStore = useChannelsStore();
  const devicesStore = useDevicesStore();
  const validSensor = (id) => isChannelReferenceCandidate(props.channel, channelsStore.all[id], devicesStore.all, 'floodSensorChannelIds');

  const supportFloodSensors = computed(() => props.channel.config.floodSensorChannelIds !== undefined);
  const floodSensorsIds = computed(() => props.channel.config.floodSensorChannelIds || []);
  const floodSensors = computed(() => floodSensorsIds.value.map((id) => channelsStore.all[id]));

  function handleNewSensor(newSensor) {
    if (!newSensor) return;
    props.channel.config.floodSensorChannelIds = [...floodSensorsIds.value, newSensor.id];
    emit('change');
  }

  function handleRemoveSensor(slot) {
    props.channel.config.floodSensorChannelIds = floodSensorsIds.value.filter((id, index) => index !== slot);
    emit('change');
  }
</script>

<style lang="scss" scoped>
  @use '../../styles/variables' as *;

  .bottom-border {
    border-bottom: 1px solid $supla-grey-light;
    &:last-child {
      border: 0;
    }
  }
</style>

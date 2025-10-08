<template>
  <div>
    <channel-opening-time-selector
      v-if="channel.config.timeSettingAvailable"
      v-model="channel.config.relayTimeMs"
      :times="times"
      @input="$emit('change')"
    ></channel-opening-time-selector>
    <dl>
      <dd>{{ $t('Opening sensor') }}</dd>
      <dt>
        <channels-id-dropdown
          v-model="channel.config.openingSensorChannelId"
          :params="'function=' + relatedChannelFunction"
          @input="$emit('change')"
        ></channels-id-dropdown>
      </dt>
    </dl>
  </div>
</template>

<script>
  import ChannelOpeningTimeSelector from './channel-opening-time-selector.vue';
  import ChannelsIdDropdown from '@/devices/channels-id-dropdown.vue';

  export default {
    components: {ChannelsIdDropdown, ChannelOpeningTimeSelector},
    props: ['channel', 'times', 'relatedChannelFunction'],
    data() {
      return {
        relatedChannel: undefined,
      };
    },
  };
</script>

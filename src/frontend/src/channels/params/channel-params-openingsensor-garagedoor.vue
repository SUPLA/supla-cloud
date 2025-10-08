<template>
  <div>
    <channel-params-openingsensor-any
      :channel="channel"
      related-channel-function="CONTROLLINGTHEGARAGEDOOR"
      @change="$emit('change')"
    ></channel-params-openingsensor-any>
    <dl>
      <dd>{{ $t('Partially opened sensor for channel') }}</dd>
      <dt>
        <channels-id-dropdown
          v-model="channel.config.openingSensorSecondaryChannelId"
          params="function=CONTROLLINGTHEGARAGEDOOR"
          @input="$emit('change')"
        ></channels-id-dropdown>
      </dt>
    </dl>
  </div>
</template>

<script>
  import ChannelParamsOpeningsensorAny from './channel-params-openingsensor-any.vue';
  import ChannelsIdDropdown from '@/devices/channels-id-dropdown.vue';

  export default {
    components: {ChannelsIdDropdown, ChannelParamsOpeningsensorAny},
    props: ['channel'],
    watch: {
      'channel.config.openingSensorChannelId'() {
        if (this.channel.config.openingSensorChannelId == this.channel.config.openingSensorSecondaryChannelId) {
          this.channel.config.openingSensorSecondaryChannelId = 0;
        }
      },
      'channel.config.openingSensorSecondaryChannelId'() {
        if (this.channel.config.openingSensorChannelId == this.channel.config.openingSensorSecondaryChannelId) {
          this.channel.config.openingSensorChannelId = 0;
        }
      },
    },
  };
</script>

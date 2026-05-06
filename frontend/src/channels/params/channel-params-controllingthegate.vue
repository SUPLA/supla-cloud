<template>
  <div>
    <channel-params-controlling-any-lock
      :channel="channel"
      :times="[500, 1000, 2000]"
      related-channel-function="OPENINGSENSOR_GATE"
      @change="$emit('change')"
    ></channel-params-controlling-any-lock>
    <dl>
      <dd>{{ $t('Partial opening sensor') }}</dd>
      <dt>
        <channels-id-dropdown
          v-model="channel.config.openingSensorSecondaryChannelId"
          params="function=OPENINGSENSOR_GATE"
          @input="$emit('change')"
        ></channels-id-dropdown>
      </dt>
    </dl>
    <channel-params-controllingthegate-number-of-openclose-attempts :channel="channel" @change="$emit('change')" />
    <ChannelParamsControllingthegateClosingRule :channel="channel" @change="$emit('change')" />
  </div>
</template>

<script>
  import ChannelParamsControllingAnyLock from './channel-params-controlling-any-lock.vue';
  import ChannelsIdDropdown from '@/devices/channels-id-dropdown.vue';
  import ChannelParamsControllingthegateNumberOfOpencloseAttempts from '@/channels/params/channel-params-controllingthegate-number-of-openclose-attempts.vue';
  import ChannelParamsControllingthegateClosingRule from '@/channels/params/channel-params-controllingthegate-closing-rule.vue';

  export default {
    components: {
      ChannelParamsControllingthegateClosingRule,
      ChannelParamsControllingthegateNumberOfOpencloseAttempts,
      ChannelsIdDropdown,
      ChannelParamsControllingAnyLock,
    },
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

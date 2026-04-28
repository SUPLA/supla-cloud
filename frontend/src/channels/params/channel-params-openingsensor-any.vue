<template>
  <div>
    <dl>
      <dd>{{ $t('Fully opened sensor for channel') }}</dd>
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
  import ChannelsIdDropdown from '@/devices/channels-id-dropdown.vue';

  export default {
    components: {ChannelsIdDropdown},
    props: ['channel', 'relatedChannelFunction'],
  };
</script>

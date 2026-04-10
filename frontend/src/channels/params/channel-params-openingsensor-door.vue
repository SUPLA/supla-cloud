<template>
  <channel-params-openingsensor-any
    :channel="channel"
    related-channel-function="CONTROLLINGTHEDOORLOCK"
    @change="$emit('change')"
  ></channel-params-openingsensor-any>
</template>

<script>
  import ChannelParamsOpeningsensorAny from './channel-params-openingsensor-any.vue';

  export default {
    components: {ChannelParamsOpeningsensorAny},
    props: ['channel'],
  };
</script>

<template>
  <channel-params-controlling-any-lock
    :channel="channel"
    :times="[500, 1000, 2000, 4000, 6000, 8000, 10000, 30000, 60000, 120000]"
    related-channel-function="OPENINGSENSOR_GATEWAY"
    @change="$emit('change')"
  ></channel-params-controlling-any-lock>
</template>

<script>
  import ChannelParamsControllingAnyLock from './channel-params-controlling-any-lock.vue';

  export default {
    components: {ChannelParamsControllingAnyLock},
    props: ['channel'],
  };
</script>

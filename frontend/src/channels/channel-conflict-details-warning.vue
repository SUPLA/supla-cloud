<template>
  <div v-if="channel.conflictDetails" class="alert alert-danger">
    <span>{{ $t('Conflict') }}:{{ ' ' }}</span>
    <span v-if="channel.conflictDetails.missing">{{ $t('This channel was missing during last device registration attempt.') }}</span>
    <!-- i18n: ['Invalid channel type ({typeCaption} - {typeId}) received in registration.'] -->
    <span v-else-if="channel.conflictDetails.type">
      {{ $t('Invalid channel type ({typeCaption} - {typeId}) received in registration.', {typeCaption: typeCaption, typeId: channel.conflictDetails.type}) }}
    </span>
    <span v-else>{{ $t('The device has channel conflict and cannot work.') }}</span>
  </div>
</template>

<script>
  import {api} from '@/api/api.js';

  export default {
    props: {
      channel: Object,
    },
    data() {
      return {
        channelTypes: undefined,
      };
    },
    computed: {
      typeCaption() {
        const type = this.channelTypes?.find((type) => type.id == this.channel.conflictDetails.type);
        return type ? this.$t(type.caption) : this.$t('Unknown');
      },
    },
    mounted() {
      api.get('enum/channel-types').then((response) => (this.channelTypes = response.body));
    },
  };
</script>

<template>
  <div>
    <transition-expand>
      <div v-if="commandSent || channel.state.soundAlarmOn">
        <button type="button" :class="['btn btn-sm', {'btn-success': commandSent, 'btn-default': !commandSent}]" @click="mute()">
          <fa v-if="commandSent" icon="check" />
          <fa v-else icon="volume-xmark" />
          <span v-if="commandSent">{{ $t('Mute command sent') }}</span>
          <span v-else>{{ $t('Mute alarm') }}</span>
        </button>
      </div>
    </transition-expand>
  </div>
</template>

<script setup>
  import {ref} from 'vue';
  import {useChannelsStore} from '@/stores/channels-store';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {channelsApi} from '@/api/channels-api';

  const props = defineProps({channel: Object});
  const channelsStore = useChannelsStore();

  const commandSent = ref(false);

  function mute() {
    if (!commandSent.value) {
      commandSent.value = true;
      channelsApi
        .muteAlarm(props.channel)
        .then(() => {
          commandSent.value = setTimeout(() => {
            channelsStore.fetchStates().finally(() => (commandSent.value = false));
          }, 4000);
        })
        .catch(() => (commandSent.value = false));
    }
  }
</script>

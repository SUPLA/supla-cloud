<template>
  <div>
    <transition-expand>
      <div v-if="showMuteButton">
        <button
          type="button"
          :disabled="loading || !canMute"
          :class="['btn btn-sm', {'btn-success': commandSent, 'btn-default': !commandSent}]"
          @click="mute()"
        >
          <button-loading-dots v-if="loading" />
          <fa v-else-if="commandSent" icon="check" />
          <fa v-else icon="volume-xmark" />
          <span v-if="loading">{{ $t('Sending') }}</span>
          <span v-else-if="commandSent">{{ $t('Mute command sent') }}</span>
          <span v-else>{{ $t('Mute alarm') }}</span>
        </button>
      </div>
    </transition-expand>
    <CalcfgSubmissionFeedback :message="submissionMessage" />
  </div>
</template>

<script setup>
  import {computed, onUnmounted, ref} from 'vue';
  import {useTimeoutFn} from '@vueuse/core';
  import {useChannelsStore} from '@/stores/channels-store';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {channelsApi} from '@/api/channels-api';
  import ButtonLoadingDots from '@/common/gui/loaders/button-loading-dots.vue';
  import CalcfgSubmissionFeedback from '@/devices/details/calcfg-submission-feedback.vue';
  import {canSubmitCalcfgAction, getCalcfgActionResult, getCalcfgSubmissionMessage} from '@/devices/details/calcfg-submission.js';

  defineOptions({compatConfig: {MODE: 3}});
  const props = defineProps({channel: Object});
  const channelsStore = useChannelsStore();
  const commandSent = ref(false);
  const loading = ref(false);
  const submissionMessage = ref('');
  const canMute = computed(() => canSubmitCalcfgAction(props.channel, 'muteAlarm'));
  const showMuteButton = computed(
    () => commandSent.value || props.channel.state?.soundAlarmOn || (!props.channel.connected && props.channel.calcfgQueueableActions?.muteAlarm)
  );

  const {start: scheduleStateRefresh, stop: cancelStateRefresh} = useTimeoutFn(
    () => channelsStore.fetchStates().finally(() => (commandSent.value = false)),
    4000,
    {immediate: false}
  );
  onUnmounted(cancelStateRefresh);

  async function mute() {
    if (loading.value || !canMute.value) return;
    cancelStateRefresh();
    commandSent.value = false;
    loading.value = true;
    submissionMessage.value = '';
    try {
      const response = await channelsApi.muteAlarm(props.channel);
      submissionMessage.value = getCalcfgSubmissionMessage(getCalcfgActionResult(response)) || '';
      if (!submissionMessage.value) {
        commandSent.value = true;
        scheduleStateRefresh();
      }
    } finally {
      loading.value = false;
    }
  }
</script>

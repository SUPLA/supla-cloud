<template>
  <div v-if="device.flags.identifyDeviceAvailable" v-tooltip="disabledReason">
    <button class="btn btn-default btn-block btn-wrapped" type="button" :disabled="!!disabledReason || loading" @click="identifyDevice()">
      <button-loading-dots v-if="loading" />
      <span v-else>{{ $t('Identify device') }}</span>
    </button>
    <transition-expand>
      <div v-if="timer" class="text-center mt-2">
        {{ $t('Identify request sent.') }}
      </div>
      <CalcfgSubmissionFeedback :message="submissionMessage" />
    </transition-expand>
  </div>
</template>

<script>
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {mapState} from 'pinia';
  import {useDevicesStore} from '@/stores/devices-store';
  import {api} from '@/api/api.js';
  import ButtonLoadingDots from '@/common/gui/loaders/button-loading-dots.vue';
  import CalcfgSubmissionFeedback from './calcfg-submission-feedback.vue';
  import {canSubmitCalcfgAction, getCalcfgActionResult, getCalcfgSubmissionMessage} from './calcfg-submission.js';

  export default {
    compatConfig: {MODE: 3},
    components: {ButtonLoadingDots, TransitionExpand, CalcfgSubmissionFeedback},
    props: ['device'],
    data() {
      return {
        loading: false,
        timer: undefined,
        lastPairingResult: null,
        submissionMessage: '',
      };
    },
    methods: {
      identifyDevice() {
        this.loading = true;
        this.submissionMessage = '';
        clearTimeout(this.timer);
        this.timer = undefined;
        api
          .patch('iodevices/' + this.device.id, {action: 'identifyDevice'})
          .then((response) => {
            const result = getCalcfgActionResult(response);
            this.submissionMessage = getCalcfgSubmissionMessage(result) || '';
            if (!this.submissionMessage) {
              this.timer = setTimeout(() => (this.timer = undefined), 3000);
            }
          })
          .catch(() => undefined)
          .finally(() => (this.loading = false));
      },
    },
    computed: {
      ...mapState(useDevicesStore, {devices: 'all'}),
      isConnected() {
        return this.devices[this.device.id]?.connected;
      },
      disabledReason() {
        if (!canSubmitCalcfgAction({...this.device, connected: this.isConnected}, 'identifyDevice')) {
          return this.$t('Device is disconnected.');
        } else if (this.device.hasPendingChanges) {
          return this.$t('Save or discard configuration changes first.');
        } else {
          return '';
        }
      },
    },
    beforeUnmount() {
      clearTimeout(this.timer);
    },
  };
</script>

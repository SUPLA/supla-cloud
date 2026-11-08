<template>
  <div v-if="device.flags.enterConfigurationModeAvailable" v-tooltip="disabledReason">
    <button class="btn btn-default btn-block btn-wrapped" type="button" :disabled="!!disabledReason" @click="enterConfirm = true">
      {{ $t('Switch the device to configuration mode') }}
    </button>
    <modal-confirm
      v-if="enterConfirm"
      class="modal-warning"
      :loading="loading"
      :header="$t('Are you sure?')"
      @confirm="enterConfigurationMode()"
      @cancel="enterConfirm = false"
    >
      <p>{{ $t('After the device enters the configuration mode, it will not be accessible on your account.') }}</p>
      <p>
        {{
          $t(
            'It will start to broadcast its own access point that is meant to be connected with your smartphone to configure its connection settings and/or update the firmware.'
          )
        }}
      </p>
      <p>{{ $t('If you fail to connect this AP for several minutes, it will restart itself and use the last settings.') }}</p>
      <p v-if="device.sleepModeEnabled">
        <strong>{{ $t('The device might be in a sleep mode.') }}</strong>
        {{ $t('If the device is not awaken now, it will enter the configuration mode at the next wake up.') }}
      </p>
    </modal-confirm>
    <CalcfgSubmissionFeedback :message="submissionMessage" />
  </div>
</template>

<script>
  import {successNotification} from '@/common/notifier';
  import {mapState} from 'pinia';
  import {useDevicesStore} from '@/stores/devices-store';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import {api} from '@/api/api.js';
  import CalcfgSubmissionFeedback from './calcfg-submission-feedback.vue';
  import {canSubmitCalcfgAction, getCalcfgActionResult, getCalcfgSubmissionMessage} from './calcfg-submission.js';

  export default {
    compatConfig: {MODE: 3},
    components: {ModalConfirm, CalcfgSubmissionFeedback},
    props: ['device'],
    data() {
      return {
        enterConfirm: false,
        loading: false,
        submissionMessage: '',
      };
    },
    methods: {
      enterConfigurationMode() {
        this.loading = true;
        this.submissionMessage = '';
        api
          .patch('iodevices/' + this.device.id, {action: 'enterConfigurationMode'})
          .then((response) => {
            const result = getCalcfgActionResult(response);
            this.submissionMessage = getCalcfgSubmissionMessage(result) || '';
            if (this.submissionMessage) {
              this.enterConfirm = false;
              this.loading = false;
            } else {
              setTimeout(() => this.assumeEntered(), 5000);
            }
          })
          .catch(() => (this.loading = false));
      },
      assumeEntered() {
        this.enterConfirm = false;
        this.loading = false;
        successNotification(this.$t('Device should be now in configuration mode.'));
      },
    },
    computed: {
      ...mapState(useDevicesStore, {devices: 'all'}),
      isConnected() {
        return this.devices[this.device.id]?.connected;
      },
      disabledReason() {
        if (!canSubmitCalcfgAction({...this.device, connected: this.isConnected}, 'enterConfigurationMode')) {
          return this.$t('Device is disconnected.');
        } else if (this.device.hasPendingChanges) {
          return this.$t('Save or discard configuration changes first.');
        } else {
          return '';
        }
      },
    },
  };
</script>

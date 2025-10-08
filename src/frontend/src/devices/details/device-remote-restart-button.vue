<template>
  <div v-if="device.flags.remoteRestartAvailable" v-tooltip="disabledReason">
    <button class="btn btn-default btn-block btn-wrapped" type="button" :disabled="!!disabledReason" @click="restartConfirm = true">
      {{ $t('Restart the device') }}
    </button>
    <modal-confirm
      v-if="restartConfirm"
      class="modal-warning"
      :loading="loading"
      :header="$t('Are you sure?')"
      @confirm="restartDevice()"
      @cancel="restartConfirm = false"
    >
      <p>{{ $t('This action will result in restarting the device. It will not be available for a while.') }}</p>
    </modal-confirm>
  </div>
</template>

<script>
  import {successNotification} from '@/common/notifier';
  import {mapState} from 'pinia';
  import {useDevicesStore} from '@/stores/devices-store';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {ModalConfirm},
    props: ['device'],
    data() {
      return {
        restartConfirm: false,
        loading: false,
      };
    },
    methods: {
      restartDevice() {
        this.loading = true;
        api
          .patch('iodevices/' + this.device.id, {action: 'restartDevice'})
          .then(() => setTimeout(() => this.assumeEntered(), 5000))
          .catch(() => (this.loading = false));
      },
      assumeEntered() {
        this.restartConfirm = false;
        this.loading = false;
        successNotification(this.$t('Successful'), this.$t('Device should be restarting now.'));
      },
    },
    computed: {
      ...mapState(useDevicesStore, {devices: 'all'}),
      isConnected() {
        return this.devices[this.device.id]?.connected;
      },
      disabledReason() {
        if (!this.isConnected) {
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

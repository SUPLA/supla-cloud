<template>
  <div v-if="device.locked" class="container">
    <div class="row">
      <div class="col-lg-6 col-lg-offset-3">
        <h2 class="no-margin-top">{{ $t('The device is locked') }}</h2>
        <p>
          {{
            $t(
              'This device should be distributed and installed by the authorized installers only. If you are not an installer of the SUPLA devices and you do not own a valid installer certificate, please return this device to the seller.'
            )
          }}
        </p>
        <p>{{ $t('Please provide the installer e-mail address to request the device unlock.') }}</p>
        <form v-if="!unlockPending" @submit.prevent="sendUnlockRequest()">
          <div class="form-group form-group-lg">
            <label for>{{ $t('Installer e-mail address') }}</label>
            <input v-model="email" type="email" class="form-control form-control-lg text-center" required />
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-green" :disabled="unlocking">
              <button-loading-dots v-if="unlocking" />
              <span v-else>
                <fa icon="unlock" />
                {{ $t('Request device unlock') }}
              </span>
            </button>
          </div>
        </form>
        <div v-else class="alert alert-info">
          {{ $t('You have requested the device to be unlocked by the installer. We will notify you when they confirm your request.') }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import 'vue-slider-component/theme/antd.css';
  import {api} from '@/api/api.js';
  import ButtonLoadingDots from '@/common/gui/loaders/button-loading-dots.vue';

  export default {
    components: {ButtonLoadingDots},
    props: {
      device: Object,
    },
    data() {
      return {
        email: '',
        unlocking: false,
        unlockPending: false,
      };
    },
    methods: {
      sendUnlockRequest() {
        this.unlocking = true;
        api
          .patch(`iodevices/${this.device.id}`, {action: 'unlock', email: this.email})
          .then(() => (this.unlockPending = true))
          .finally(() => (this.unlocking = false));
      },
    },
  };
</script>

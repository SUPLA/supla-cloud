<template>
  <modal class="modal-warning" :header="$t('We will miss you')" @cancel="$emit('cancel')">
    <p class="text-center">
      {{
        $t(
          'Deleting your account will result also in deletion of all your data, including your connected devices, configured channels, direct links and measurement history. Deleting an account is irreversible.'
        )
      }}
    </p>
    <p class="text-center">{{ $t('In order to confirm account deletion, enter your password.') }}</p>
    <input id="password" v-model="password" type="password" autocomplete="new-password" class="form-control" />
    <label for="password">{{ $t('Password') }}</label>
    <template #footer>
      <button-loading-dots v-if="loading"></button-loading-dots>
      <div v-else>
        <a class="btn btn-grey" @click="$emit('cancel')">
          {{ $t('Cancel') }}
        </a>
        <a class="btn btn-red-outline" @click="deleteAccount()">
          {{ $t('I confirm! Delete my account.') }}
        </a>
      </div>
    </template>
  </modal>
</template>

<script>
  import {errorNotification, successNotification} from '../common/notifier';
  import ButtonLoadingDots from '../common/gui/loaders/button-loading-dots.vue';
  import Modal from '@/common/modal.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {Modal, ButtonLoadingDots},
    data() {
      return {
        password: '',
        loading: false,
      };
    },
    methods: {
      deleteAccount() {
        if (!this.password) {
          return errorNotification(this.$t('Incorrect password'));
        }
        this.loading = true;
        api
          .patch(`users/current`, {action: 'delete', password: this.password})
          .then(() => {
            successNotification(this.$t('We have sent you an e-mail message with a delete confirmation link. Just to be sure!'));
            this.$emit('cancel');
            document.getElementById('logoutButton').dispatchEvent(new MouseEvent('click'));
          })
          .finally(() => (this.loading = false));
      },
    },
  };
</script>

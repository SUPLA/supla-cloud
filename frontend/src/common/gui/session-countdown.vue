<template>
  <span v-if="expirationTimestamp">
    <span v-if="secondsLeft > 0" class="text-muted">
      <i18n-t keypath="Your session will expire in {0}." tag="span">
        <span>{{ timeLeft }}</span>
      </i18n-t>
      <a v-if="secondsLeft < 300" @click="show()">{{ $t('extend') }}</a>
    </span>
    <modal
      v-if="showDialog"
      class="modal-confirm"
      :header="$t('Your session is about to expire')"
      :class="['text-center session-countdown-modal', {expiring: secondsLeft < 60}]"
    >
      <p>
        <i18n-t keypath="Your session will expire in {0}." tag="span">
          <span>{{ timeLeft }}</span>
        </i18n-t>
      </p>
      <div class="form-group">
        <p>{{ $t('Enter your password to prevent automatic logout.') }}</p>
      </div>
      <form @submit.prevent="extendSession()">
        <div class="form-group text-left">
          <input v-model="currentUserStore.username" type="text" name="username" class="form-control" readonly />
          <label>{{ $t('Your email') }}</label>
        </div>
        <div class="form-group text-left">
          <input id="extend-password" v-model="password" v-focus="true" type="password" required class="form-control" />
          <label for="extend-password">{{ $t('Password') }}</label>
        </div>
        <button class="hidden" type="submit"></button>
      </form>
      <div v-if="error" class="alert alert-danger">{{ $t('Incorrect password') }}</div>
      <template #footer>
        <button-loading-dots v-if="loading"></button-loading-dots>
        <div v-else>
          <div class="pull-left">
            <a class="btn btn-default" @click="logout()">
              {{ $t('Sign Out') }}
            </a>
          </div>
          <a class="cancel" @click="cancel()">
            <i class="pe-7s-close"></i>
          </a>
          <a class="confirm" @click="extendSession()">
            <i class="pe-7s-check"></i>
          </a>
        </div>
      </template>
    </modal>
  </span>
</template>

<script>
  import AppState from '../../router/app-state';
  import {DateTime} from 'luxon';
  import {useCurrentUserStore} from '@/stores/current-user-store';
  import {mapStores} from 'pinia';
  import ButtonLoadingDots from '@/common/gui/loaders/button-loading-dots.vue';
  import Modal from '@/common/modal.vue';

  export default {
    components: {Modal, ButtonLoadingDots},
    data() {
      return {
        showDialog: false,
        loading: false,
        password: undefined,
        error: false,
        expirationTimestamp: undefined,
        interval: undefined,
        secondsLeft: undefined,
      };
    },
    mounted() {
      this.synchronizeExpirationTime();
      this.interval = setInterval(() => this.countdown(), 1000);
    },
    computed: {
      timeLeft() {
        if (this.secondsLeft > 0) {
          const minutes = Math.floor(this.secondsLeft / 60);
          const seconds = this.secondsLeft % 60;
          return (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
        } else {
          return '';
        }
      },
      ...mapStores(useCurrentUserStore),
    },
    beforeUnmount() {
      if (this.interval) {
        clearInterval(this.interval);
      }
    },
    methods: {
      synchronizeExpirationTime() {
        const expirationTime = this.currentUserStore.tokenExpiration;
        if (expirationTime) {
          const timestamp = DateTime.fromISO(expirationTime).toSeconds();
          if (timestamp > new Date().getTime() / 1000) {
            this.expirationTimestamp = timestamp;
            this.countdown();
          }
        }
      },
      cancel() {
        this.showDialog = false;
        this.password = undefined;
        this.error = false;
      },
      show() {
        this.showDialog = true;
        this.$nextTick(() => this.countdown());
      },
      extendSession() {
        this.loading = true;
        this.error = false;
        this.currentUserStore
          .authenticate(this.currentUserStore.username, this.password)
          .then(() => this.synchronizeExpirationTime())
          .then(() => this.cancel())
          .catch(() => (this.error = true))
          .finally(() => (this.loading = false));
      },
      countdown() {
        if (this.expirationTimestamp) {
          this.secondsLeft = this.expirationTimestamp - Math.floor(new Date().getTime() / 1000);
          if (this.secondsLeft < 60 && !this.showDialog) {
            this.show();
          }
          if (this.secondsLeft <= 0) {
            AppState.addTask('sessionExpired', true);
            this.logout();
          }
        }
      },
      logout() {
        this.cancel();
        clearInterval(this.interval);
        this.expirationTimestamp = undefined;
        document.getElementById('logoutButton').dispatchEvent(new MouseEvent('click'));
      },
    },
  };
</script>

<style lang="scss">
  .session-countdown-modal {
    &.expiring {
      .cancel {
        display: none;
      }
    }
  }
</style>

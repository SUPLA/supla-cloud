<template>
  <modal-confirm
    v-if="!frontendConfigStore.backendAndFrontendVersionMatches && !isDev && shown"
    class="modal-warning green-confirm-button"
    :header="$t('Update in progress')"
    @confirm="redirect()"
    @cancel="shown = false"
  >
    <p>{{ $t('The SUPLA infrastructure is being updated.') }}</p>
    <p>{{ $t('For a short period of time, it is recommended for you to use the {suplaUrl} address.', {suplaUrl: frontendConfigStore.config.suplaUrl}) }}</p>
    <p>{{ $t('Do you want to be redirected there? You may be asked to authenticate again.') }}</p>
  </modal-confirm>
</template>

<script>
  import {mapStores} from 'pinia';
  import {useFrontendConfigStore} from '@/stores/frontend-config-store';
  import ModalConfirm from '@/common/modal-confirm.vue';

  export default {
    components: {ModalConfirm},
    data() {
      return {
        shown: true,
      };
    },
    methods: {
      redirect() {
        window.location.href = this.frontendConfigStore.config.suplaUrl;
      },
    },
    computed: {
      isDev() {
        return ['dev', 'e2e'].includes(this.frontendConfigStore.env);
      },
      ...mapStores(useFrontendConfigStore),
    },
  };
</script>

<style scoped lang="scss">
  @use '../../styles/variables' as *;
  @use 'sass:color';

  .alert {
    width: 90%;
    max-width: 300px;
    margin: 0 auto;
    position: fixed;
    bottom: 45px;
    right: 5px;
    background: $supla-yellow;
    border-color: color.adjust($supla-yellow, $lightness: -10%);
  }
</style>

<style>
  .hide-cookies-warning .cookie-warning {
    display: none;
  }
</style>

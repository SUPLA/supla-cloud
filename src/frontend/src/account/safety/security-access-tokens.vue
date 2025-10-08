<template>
  <div class="container">
    <loading-cover :loading="accessTokens === undefined">
      <h2>{{ $t('Active web sessions') }}</h2>
      <table v-if="accessTokens" class="table table-striped">
        <thead>
          <tr>
            <th>{{ $t('Device') }}</th>
            <th>{{ $t('Expires at') }}</th>
            <th>{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="accessToken in webappTokens" :key="accessToken.id">
            <td>
              <span v-if="accessToken.issuerSystem">
                {{ accessToken.issuerSystem.browserName || $t('Unknown browser') }}
                {{ accessToken.issuerSystem.browserVersion || '' }}
                {{ accessToken.issuerSystem.os || '' }}
                <span v-if="accessToken.issuerSystem.device !== 'Other'">{{ accessToken.issuerSystem.device || '' }}</span>
              </span>
              <em v-else>{{ $t('unknown') }}</em>
              <span v-if="accessToken.issuerIp">/ {{ accessToken.issuerIp }}</span>
            </td>
            <td>
              {{ formatDateTime(accessToken.expiresAt) }}
            </td>
            <td>
              <a class="btn btn-default btn-xs" @click="accessTokenToDelete = accessToken">
                <fa :icon="faSignOut()" />
                {{ $t('Log out') }}
              </a>
            </td>
          </tr>
        </tbody>
      </table>
      <h2>{{ $t('Active applications') }}</h2>
      <table v-if="accessTokens && applicationTokens.length > 0" class="table table-striped">
        <thead>
          <tr>
            <th>{{ $t('Application') }}</th>
            <th>{{ $t('Expires at') }}</th>
            <th>{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="accessToken in applicationTokens" :key="accessToken.id">
            <td>
              {{ accessToken.apiClient.name }}
            </td>
            <td>
              {{ formatDateTime(accessToken.expiresAt) }}
            </td>
            <td></td>
          </tr>
        </tbody>
      </table>
      <empty-list-placeholder v-else-if="accessTokens" />
    </loading-cover>
    <modal-confirm
      v-if="accessTokenToDelete"
      class="modal-warning"
      :header="$t('Are you sure you want to log out this device?')"
      :loading="deleting"
      @confirm="deleteAccessToken(accessTokenToDelete)"
      @cancel="accessTokenToDelete = undefined"
    >
      {{ $t('The device that uses this session will loose access to the SUPLA Cloud and will need to reauthenticate.') }}
    </modal-confirm>
  </div>
</template>

<script>
  import {successNotification} from '@/common/notifier';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {formatDateTime} from '@/common/filters-date';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import {api} from '@/api/api.js';
  import {faSignOut} from '@fortawesome/free-solid-svg-icons';

  export default {
    components: {ModalConfirm, EmptyListPlaceholder, LoadingCover},
    data() {
      return {
        accessTokens: undefined,
        deleting: false,
        accessTokenToDelete: undefined,
      };
    },
    computed: {
      webappTokens() {
        return this.accessTokens?.filter((token) => token.isForWebapp);
      },
      applicationTokens() {
        return this.accessTokens?.filter((token) => !token.isForWebapp);
      },
    },
    mounted() {
      this.fetchTokens();
    },
    methods: {
      faSignOut() {
        return faSignOut;
      },
      formatDateTime,
      fetchTokens() {
        this.accessTokens = undefined;
        return api.get('access-tokens').then((response) => {
          this.accessTokens = response.body;
        });
      },
      deleteAccessToken(token) {
        this.deleting = true;
        api
          .delete(`access-tokens/${token.id}`)
          .then(() => successNotification(this.$t('Success'), this.$t('Selected device has been logged out.')))
          .then(() => this.fetchTokens())
          .then(() => (this.accessTokenToDelete = undefined))
          .finally(() => (this.deleting = false));
      },
    },
  };
</script>

<style scoped></style>

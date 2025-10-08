<template>
  <div class="container">
    <h5>{{ $t('This is the list of applications you have granted access to your account.') }}</h5>
    <loading-cover :loading="!authorizedApps">
      <table v-if="authorizedApps && authorizedApps.length" class="table table-striped">
        <thead>
          <tr>
            <th>{{ $t('Name') }}</th>
            <th>{{ $t('Scope') }}</th>
            <th>{{ $t('Authorized') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="authorizedApp in authorizedApps" :key="authorizedApp.id">
            <td>{{ authorizedApp.apiClient.name }}</td>
            <td>
              <oauth-scope-label :scope="authorizedApp.scope"></oauth-scope-label>
            </td>
            <td>{{ formatDateTime(authorizedApp.authorizationDate) }}</td>
            <td class="text-right">
              <button type="button" class="btn btn-red btn-xs" @click="appToRevoke = authorizedApp">
                <fa icon="times-circle" />
                {{ $t('Revoke') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <empty-list-placeholder v-else-if="authorizedApps" />
    </loading-cover>
    <modal-confirm
      v-if="appToRevoke"
      class="modal-warning"
      :header="$t('Are you sure you want to revoke permissions granted to this OAuth application?')"
      :loading="revoking"
      @confirm="revokeApp(appToRevoke)"
      @cancel="appToRevoke = undefined"
    >
      {{
        $t('{applicationName} will not be able to interact with your account until you grant the access again.', {applicationName: appToRevoke.apiClient.name})
      }}
    </modal-confirm>
  </div>
</template>

<script>
  import OauthScopeLabel from '../integrations/oauth-scope-label.vue';
  import {api} from '@/api/api.js';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import {formatDateTime} from '@/common/filters-date.js';

  export default {
    components: {ModalConfirm, EmptyListPlaceholder, LoadingCover, OauthScopeLabel},
    data() {
      return {
        authorizedApps: undefined,
        appToRevoke: undefined,
        revoking: false,
      };
    },
    mounted() {
      api.get('oauth-authorized-clients?include=client').then((response) => {
        this.authorizedApps = response.body;
      });
    },
    methods: {
      formatDateTime,
      revokeApp(app) {
        this.revoking = true;
        api
          .delete_('oauth-authorized-clients/' + app.id)
          .then(() => this.authorizedApps.splice(this.authorizedApps.indexOf(app), 1))
          .then(() => (this.appToRevoke = undefined))
          .finally(() => (this.revoking = false));
      },
    },
  };
</script>

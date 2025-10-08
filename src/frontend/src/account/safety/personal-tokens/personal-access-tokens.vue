<template>
  <div class="container">
    <div class="clearfix left-right-header">
      <div>
        <h5>{{ $t('Personal access tokens function like ordinary OAuth access tokens but they have no expiration time.') }}</h5>
        <div class="form-group">
          <div class="btn-group">
            <a href="https://github.com/SUPLA/supla-cloud/wiki/Integrations#personal-access-tokens" class="btn btn-white">
              {{ $t('Full documentation') }} @ GitHub
            </a>
            <a :href="withBaseUrl('/api-docs/docs.html')" target="_blank" class="btn btn-white">
              {{ $t('API documentation') }}
              <i class="pe-7s-exapnd2"></i>
            </a>
          </div>
        </div>
      </div>
      <div>
        <a v-if="!generatingNewToken" class="btn btn-white" @click="generatingNewToken = true">
          <i class="pe-7s-plus"></i>
          {{ $t('Generate new token') }}
        </a>
      </div>
    </div>
    <div v-if="generatingNewToken" class="well">
      <h3 class="no-margin-top">{{ $t('Generate new token') }}</h3>
      <personal-access-token-generate-form @generated="onNewToken($event)" @cancel="generatingNewToken = false"></personal-access-token-generate-form>
    </div>
    <div v-if="latestToken" class="alert alert-info">
      <div class="form-group">
        {{ $t('A new personal access token has been generated.') }}
        <strong>{{ $t('Make sure to copy it now because you won’t be able to see it again!') }}</strong>
      </div>
      <pre><code>{{ latestToken.token }}</code></pre>
      <copy-button :text="latestToken.token"></copy-button>
    </div>
    <loading-cover :loading="!tokens">
      <table v-if="tokens && tokens.length" class="table table-striped">
        <thead>
          <tr>
            <th>{{ $t('Name') }}</th>
            <th>{{ $t('Scope') }}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="token in tokens" :key="token.id">
            <td>{{ token.name }}</td>
            <td>
              <oauth-scope-label :scope="token.scope"></oauth-scope-label>
            </td>
            <td class="text-right">
              <button type="button" class="btn btn-red" @click="tokenToRevoke = token">
                <i class="pe-7s-close-circle"></i>
                {{ $t('Revoke') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <empty-list-placeholder v-else-if="tokens"></empty-list-placeholder>
    </loading-cover>
    <modal-confirm
      v-if="tokenToRevoke"
      class="modal-warning"
      :header="$t('Are you sure you want to revoke this token?')"
      :loading="revoking"
      @confirm="revokeToken(tokenToRevoke)"
      @cancel="tokenToRevoke = undefined"
    >
      {{ $t('Any application or device that uses the {tokenName} token will not work anymore.', {tokenName: tokenToRevoke.name}) }}
    </modal-confirm>
  </div>
</template>

<script>
  import PersonalAccessTokenGenerateForm from './personal-access-token-generate-form.vue';
  import OauthScopeLabel from '../../integrations/oauth-scope-label.vue';
  import CopyButton from '../../../common/copy-button.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import {api} from '@/api/api.js';
  import {withBaseUrl} from '@/common/filters.js';

  export default {
    components: {
      ModalConfirm,
      EmptyListPlaceholder,
      LoadingCover,
      CopyButton,
      OauthScopeLabel,
      PersonalAccessTokenGenerateForm,
    },
    data() {
      return {
        tokens: undefined,
        generatingNewToken: false,
        latestToken: undefined,
        revoking: false,
        tokenToRevoke: undefined,
      };
    },
    mounted() {
      api.get('oauth-personal-tokens').then((response) => {
        this.tokens = response.body;
      });
    },
    methods: {
      withBaseUrl,
      onNewToken(token) {
        this.tokens.unshift(token);
        this.latestToken = token;
        this.generatingNewToken = false;
      },
      revokeToken(token) {
        this.revoking = true;
        api
          .delete_('oauth-personal-tokens/' + token.id)
          .then(() => this.tokens.splice(this.tokens.indexOf(token), 1))
          .then(() => (this.tokenToRevoke = undefined))
          .finally(() => (this.revoking = false));
      },
    },
  };
</script>

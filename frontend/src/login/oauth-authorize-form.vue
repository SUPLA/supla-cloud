<template>
  <div v-title="$t('OAuth authorization')">
    <div class="authorize-form">
      <div v-if="loading" class="text-center" role="status">{{ $t('Loading authorization details…') }}</div>
      <template v-else-if="presentation">
        <div class="authorization-logo">
          <span class="app-name">{{ presentation.application.name }}</span>
          <span class="arrow"><span class="pe-7s-link"></span></span>
          <img src="../assets/img/logo.svg" alt="SUPLA" />
        </div>
        <p v-if="presentation.application.description" class="text-center">
          <i18n-text :text="presentation.application.description" />
        </p>
        <h3 class="text-center">{{ $t('Authorize the application, so that it can access your account.') }}</h3>
        <div class="form-group clearfix">
          <div class="list-group scope-selector">
            <div v-for="scope in desiredAvailableScopes" :key="scope.label" class="list-group-item">
              <h4>{{ $t(scope.label) }}</h4>
              <div class="permissions">
                <div v-for="suffix in scope.suffixes" :key="`${scope.prefix}_${suffix}`" class="text-center mx-1">
                  <i :class="'pe-7s-' + icons[suffix]"></i>
                  {{ $t(scopeSuffixLabels[suffix]) }}
                  <div v-if="additionalInfo[`${scope.prefix}_${suffix}`]" class="small">
                    {{ $t(additionalInfo[`${scope.prefix}_${suffix}`]) }}
                  </div>
                </div>
              </div>
            </div>
            <div v-for="scope in unmappedScopes" :key="scope" class="list-group-item">
              <h4>{{ scope }}</h4>
            </div>
          </div>
        </div>

        <div class="alert alert-info">
          <span class="pe-7s-info"></span>
          {{ $t('After granting the access, you can always remove it by adjusting the settings in your account settings section.') }}
        </div>

        <div class="alert alert-info text-center" role="status">
          <span v-if="presentation.status === 'unauthenticated'">{{ $t('Sign in to continue') }}</span>
          <span v-else-if="presentation.status === 'consent_required'">{{ $t('Review these permissions to continue.') }}</span>
          <span v-else-if="presentation.status === 'already_authorized'">{{ $t('You have already authorized this application.') }}</span>
          <span v-if="presentation.email"> — {{ presentation.email }}</span>
        </div>

        <div v-if="privateCloudSelectionAvailable" class="private-cloud-selection">
          <label class="checkbox2 checkbox2-grey">
            <input v-model="selectPrivateCloud" type="checkbox" @change="privateCloudError = undefined" />
            {{ $t('Connect to a private SUPLA Cloud instance') }}
          </label>
          <form v-if="selectPrivateCloud" class="form-group private-cloud-form" @submit.prevent="continueToPrivateCloud">
            <label for="oauth-private-cloud">{{ $t('Private Cloud domain name') }}</label>
            <input
              id="oauth-private-cloud"
              v-model.trim="privateCloudHost"
              class="form-control"
              type="text"
              autocomplete="url"
              autocorrect="off"
              autocapitalize="none"
              required
            />
            <p class="help-block">
              {{ $t('Only domain names with an optional port number are allowed. HTTPS is required.') }}
            </p>
            <div v-if="privateCloudError" class="alert alert-warning" role="alert">{{ privateCloudError }}</div>
            <button class="btn btn-default" type="submit" :disabled="privateCloudSubmitting">
              {{ $t('Continue to private Cloud') }}
            </button>
          </form>
        </div>

        <div v-if="error" class="alert alert-warning" role="alert">{{ error }}</div>
        <form class="form-group" @submit.prevent="authorize('approve')">
          <label for="oauth-username">{{ $t('E-mail') }}</label>
          <input id="oauth-username" v-model.trim="username" class="form-control" type="email" autocomplete="username" required />
          <label for="oauth-password">{{ $t('Password') }}</label>
          <input id="oauth-password" v-model="password" class="form-control" type="password" autocomplete="current-password" required />
          <div class="buttons">
            <button class="btn btn-primary" type="submit" :disabled="submitting">
              {{ $t('Sign in and grant access') }}
            </button>
            <button class="btn btn-default" type="button" :disabled="submitting" @click="authorize('deny')">
              {{ $t('Deny') }}
            </button>
          </div>
        </form>
        <div v-if="submitting" class="text-center" role="status">{{ $t('Submitting your decision…') }}</div>
      </template>
      <div v-else-if="error" class="alert alert-warning" role="alert">{{ error }}</div>
    </div>
  </div>
</template>

<script>
  import {availableScopes, scopeId, scopeSuffixLabels} from '../account/integrations/oauth-scopes';
  import {api} from '@/api/api';
  import {useCurrentUserStore} from '@/stores/current-user-store';
  import {useFrontendConfigStore} from '@/stores/frontend-config-store';
  import {deepCopy} from '@/common/utils.js';
  import I18nText from '../account/integrations/catalog/i18n-text.vue';

  export default {
    compatConfig: {MODE: 3},
    components: {I18nText},
    computed: {
      privateCloudSelectionAvailable() {
        return !!useFrontendConfigStore().config.actAsBrokerCloud;
      },
    },
    data() {
      return {
        presentation: undefined,
        loading: true,
        error: undefined,
        username: '',
        password: '',
        submitting: false,
        selectPrivateCloud: false,
        privateCloudHost: '',
        privateCloudSubmitting: false,
        privateCloudError: undefined,
        desiredAvailableScopes: [],
        unmappedScopes: [],
        scopeSuffixLabels,
        additionalInfo: {
          account_r: 'access to your e-mail address', // i18n
        },
        icons: {
          r: 'look',
          rw: 'edit',
          ea: 'power',
          access: 'moon',
          webhook: 'call',
          broker: 'speaker',
        },
      };
    },
    mounted() {
      this.loadAuthorizationRequest();
    },
    methods: {
      async loadAuthorizationRequest(retryWithoutToken = true) {
        this.error = undefined;
        // Keep the entire original query (including encoded state and PKCE) as
        // the endpoint input; never rewrite the browser's authorization URL.
        try {
          const {body} = await api.get(`oauth/authorization-request${window.location.search}`, {skipErrorHandler: [401, 403]});
          this.presentation = body;
          this.username = body.email || '';
          const desiredScopes = [...(body.scopes?.requested || []), ...(body.scopes?.implicit || [])];
          const desiredAvailableScopes = deepCopy(availableScopes);
          desiredAvailableScopes.forEach((scope) => {
            scope.suffixes = scope.suffixes.filter((suffix) => desiredScopes.includes(scopeId(scope, suffix)));
          });
          this.desiredAvailableScopes = desiredAvailableScopes.filter((scope) => scope.suffixes.length > 0);
          const knownScopes = this.desiredAvailableScopes.flatMap((scope) => scope.suffixes.map((suffix) => scopeId(scope, suffix)));
          this.unmappedScopes = desiredScopes.filter((scope) => !knownScopes.includes(scope));
          if (body.canAutoAuthorize && useCurrentUserStore().userToken) {
            this.authorize('approve', true);
          }
        } catch (error) {
          const {status} = error || {};
          if (status === 401) {
            const currentUser = useCurrentUserStore();
            if (retryWithoutToken && currentUser.userToken) {
              currentUser.forgetOAuthToken();
              await this.loadAuthorizationRequest(false);
              return;
            }
            this.error = this.$t('Sign in to continue');
          } else if (status === 403) {
            this.error = this.$t('Access to this authorization request is unavailable.');
          } else {
            this.error = this.$t('Authorization details are temporarily unavailable. Please try again.');
          }
        } finally {
          this.loading = false;
        }
      },
      async continueToPrivateCloud() {
        if (this.privateCloudSubmitting) return;
        this.privateCloudSubmitting = true;
        this.privateCloudError = undefined;
        const fields = new FormData();
        fields.set('targetCloud', this.privateCloudHost);
        const authorizationFields = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'code_challenge', 'code_challenge_method'];
        new URLSearchParams(window.location.search).forEach((value, name) => {
          if (authorizationFields.includes(name)) fields.set(name, value);
        });

        try {
          const {body} = await api.post(`${window.location.origin}/api/oauth/private-cloud`, fields, {skipErrorHandler: true});
          const destination = new URL(body.redirectUrl);
          if (
            destination.protocol !== 'https:' ||
            destination.pathname !== '/oauth/v2/auth' ||
            destination.username ||
            destination.password ||
            destination.hash
          ) {
            throw new Error('invalid_destination');
          }
          window.location.assign(destination.toString());
        } catch (error) {
          const {body} = error || {};
          this.privateCloudError = this.privateCloudErrorMessage(body?.errorCode);
        } finally {
          this.privateCloudSubmitting = false;
        }
      },
      privateCloudErrorMessage(errorCode) {
        switch (errorCode) {
          case 'invalid_target':
            return this.$t('Enter a valid private Cloud domain name.');
          case 'invalid_client':
            return this.$t('This application is not registered on that private Cloud.');
          default:
            return this.$t('We could not connect to that private Cloud. Check the address and try again.');
        }
      },
      authorize(decision, useBrowserToken = false) {
        if (this.submitting) return;
        this.error = undefined;
        this.submitting = true;
        const form = new FormData();
        const authorizationFields = ['client_id', 'redirect_uri', 'response_type', 'scope', 'state', 'code_challenge', 'code_challenge_method'];
        new URLSearchParams(window.location.search).forEach((value, name) => {
          if (authorizationFields.includes(name)) form.set(name, value);
        });
        form.set('decision', decision);
        form.set('username', this.username);
        if (useBrowserToken) {
          form.set('target_token', useCurrentUserStore().userToken);
        } else if (decision === 'approve') {
          form.set('password', this.password);
        }

        api
          .post('oauth/authorize', form, {skipErrorHandler: true})
          .then(({body}) => {
            if (['authorized', 'denied'].includes(body.outcome) && typeof body.redirectUrl === 'string') {
              window.location.assign(body.redirectUrl);
              return;
            }
            this.error = this.authorizationErrorMessage(body?.errorCode);
          })
          .catch(({body, status} = {}) => {
            if (useBrowserToken && body?.errorCode === 'password_required') {
              this.presentation.status = 'consent_required';
            } else if (useBrowserToken && body?.errorCode === 'invalid_token') {
              useCurrentUserStore().forgetOAuthToken();
              this.presentation = undefined;
              this.loading = true;
              this.loadAuthorizationRequest(false);
              return;
            }
            if (status === 403) {
              this.error = this.$t('Access to this authorization request is unavailable.');
            } else if (status === undefined) {
              this.error = this.$t('Authorization is temporarily unavailable. Please try again.');
            } else {
              this.error = this.authorizationErrorMessage(body?.errorCode);
            }
          })
          .finally(() => {
            this.submitting = false;
            this.password = '';
          });
      },
      authorizationErrorMessage(errorCode) {
        switch (errorCode) {
          case 'invalid_credentials':
            return this.$t('The e-mail or password is incorrect.');
          case 'locked':
            return this.$t('Too many sign-in attempts. Please try again later.');
          case 'disabled':
            return this.$t('This account is disabled.');
          case 'password_required':
            return this.$t('Enter your password to approve these permissions.');
          case 'unsupported_authorization_api':
            return this.$t('This account cloud needs an update before it can authorize this application.');
          case 'target_unavailable':
            return this.$t('The account cloud is temporarily unavailable. Please try again.');
          case 'unknown_target':
            return this.$t('We could not find an account cloud for this e-mail address. Check the address and try again.');
          case 'invalid_client':
            return this.$t('This application is not registered on the account cloud and cannot be authorized.');
          case 'invalid_request':
            return this.$t('This authorization request is invalid. Return to the application and try again.');
          case 'invalid_token':
            return this.$t('Your sign-in has expired. Sign in again to continue.');
          default:
            return this.$t('Authorization could not be completed. Please try again.');
        }
      },
    },
  };
</script>

<style lang="scss">
  @use '../styles/variables' as *;
  @use '../styles/mixins' as *;

  .authorize-form {
    width: 90%;
    max-width: 600px;
    margin: 0 auto;
    padding-top: 10px;
    .authorization-logo {
      .app-name {
        font-size: 40px;
        display: inline-block;
        max-width: 55%;
        vertical-align: middle;
      }
      .arrow {
        font-size: 50px;
        font-weight: bold;
        display: inline-block;
        padding: 0 20px;
        vertical-align: middle;
      }
      text-align: center;
      margin-bottom: 40px;
      img {
        width: 150px;
        height: 150px;
      }
    }
    .buttons {
      margin-top: 30px;
    }
    .scope-selector {
      display: flex;
      flex-flow: row wrap;
      > div {
        flex-grow: 1;
        border-radius: 0 !important;
        h4 {
          margin-top: 0;
        }
        .permissions {
          display: flex;
          justify-content: space-evenly;
          i {
            display: block;
            text-align: center;
            font-size: 2em;
            margin-bottom: 5px;
            color: $supla-green;
          }
        }
      }
    }
  }
</style>

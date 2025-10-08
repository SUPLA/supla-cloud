<template>
  <div v-title="$t('OAuth authorization')">
    <div class="authorize-form">
      <div class="authorization-logo">
        <span class="app-name">{{ clientName }}</span>
        <span class="arrow"><span class="pe-7s-link"></span></span>
        <img src="../assets/img/logo.svg" alt="SUPLA" />
      </div>
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
        </div>
      </div>

      <div class="alert alert-info">
        <span class="pe-7s-info"></span>
        {{ $t('After granting the access, you can always remove it by adjusting the settings in your account settings section.') }}
      </div>

      <div class="buttons">
        <form :name="formName" method="post" action="/oauth/v2/auth">
          <input type="submit" name="rejected" class="btn btn-white btn-lg" :value="$t('Decline')" />
          <input type="submit" name="accepted" class="btn btn-green btn-lg pull-right" :value="$t('Grant access')" />
          <input
            v-for="field in formFields"
            :id="`${formName}_${field.name}`"
            :key="field.name"
            type="hidden"
            :name="`${formName}[${field.name}]`"
            :value="field.value"
          />
        </form>
      </div>
    </div>
  </div>
</template>

<script>
  import {addImplicitScopes, arrayOfScopes, availableScopes, scopeId, scopeSuffixLabels} from '../account/integrations/oauth-scopes';
  import {setGuiLocale} from '@/locale';
  import {deepCopy} from '@/common/utils.js';

  export default {
    data() {
      return {
        desiredScopes: undefined,
        clientName: undefined,
        locale: undefined,
        formName: undefined,
        formFields: [],
        desiredAvailableScopes: [],
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
      this.readRequestFromWindow();
      const desiredScopes = addImplicitScopes(arrayOfScopes(this.desiredScopes));
      const desiredAvailableScopes = deepCopy(availableScopes);
      desiredAvailableScopes.forEach((scope) => (scope.suffixes = scope.suffixes.filter((suffix) => desiredScopes.indexOf(scopeId(scope, suffix)) !== -1)));
      this.desiredAvailableScopes = desiredAvailableScopes.filter((scope) => scope.suffixes.length > 0);
      if (this.locale) {
        setGuiLocale(this.locale);
      }
    },
    methods: {
      readRequestFromWindow() {
        this.desiredScopes = window.oauthAuthorizeRequest?.desiredScopes;
        this.clientName = window.oauthAuthorizeRequest?.clientName;
        this.locale = window.oauthAuthorizeRequest?.locale;
        this.formName = window.oauthAuthorizeRequest?.formName;
        this.formFields = window.oauthAuthorizeRequest?.formFields;
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

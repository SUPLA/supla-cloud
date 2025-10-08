<template>
  <form
    ref="loginForm"
    v-title="$t('Login')"
    :action="askForTargetCloud ? '/oauth/v2/broker_login' : '/oauth/v2/auth_login'"
    method="post"
    :class="'centered-form-container login-oauth-form ' + (askForTargetCloud ? 'login-oauth-form-broker' : '')"
    @submit="authenticating = true"
  >
    <login-form
      :authenticating="authenticating"
      :oauth="true"
      :error="error"
      :intitial-username="lastUsername"
      :submit-button-text="askForTargetCloud ? $t('Proceed') : ''"
    >
      <template #aboveForm>
        <div v-if="clientName">
          <div v-if="isGoogle">
            <h4 class="client-name-prompt">
              {{ $t('By signing in, you are authorizing {clientName} to control your devices.', {clientName: clientName}) }}
            </h4>
          </div>
          <div v-else>
            <h4 class="client-name-prompt">
              {{ $t('{clientName} wants to access your account.', {clientName: clientName}) }}
            </h4>
            <h6 class="mb-3 text-muted text-center">
              {{ $t('By signing in, you are authorizing {clientName} to control your devices.', {clientName: clientName}) }}
            </h6>
          </div>
        </div>
      </template>
      <div v-if="askForTargetCloud">
        <div class="form-group text-center">
          <label>
            <label class="checkbox2 checkbox2-grey">
              <input v-model="ownCloud" type="checkbox" @change="error = undefined" />
              {{ $t('Connection with a private instance of the SUPLA cloud') }}
            </label>
          </label>
        </div>
        <transition-expand>
          <div v-if="ownCloud" class="form-group form-group-lg">
            <span class="input-group">
              <span class="input-group-addon">
                <span class="pe-7s-global"></span>
              </span>
              <input
                v-model="targetCloud"
                type="text"
                required
                autocorrect="off"
                autocapitalize="none"
                :placeholder="$t('Private Cloud domain name')"
                name="targetCloud"
                class="form-control"
              />
            </span>
            <span class="help-block">
              {{ $t('Only domain names with an optional port number are allowed. E.g. mysupla.org or mysupla.org:88. HTTPS is required.') }}
            </span>
          </div>
        </transition-expand>
        <transition name="fade">
          <div v-if="error" class="error">
            <div v-if="error == 'autodiscover_fail'">
              <div v-if="ownCloud">
                <strong>{{ $t('We could not connect to your SUPLA Cloud instance.') }}</strong>
                {{ $t('Your instance is not registered or you are trying to authorize an application that is not public.') }}
              </div>
              <div v-else>
                <strong>{{ $t('We were not able to find your account.') }}</strong>
                {{ $t('If you are sure you have an account on cloud.supla.org, check if the application you are trying to authorize is public.') }}
              </div>
            </div>
            <div v-if="error == 'private_cloud_fail'">
              {{ $t('Your private SUPLA Cloud instance is not available. Make sure your server is online and your https connection works properly.') }}
            </div>
          </div>
        </transition>
      </div>
    </login-form>
  </form>
</template>

<script>
  import LoginForm from './login-form.vue';
  import TransitionExpand from '../common/gui/transition-expand.vue';

  export default {
    components: {TransitionExpand, LoginForm},
    data() {
      return {
        ownCloud: false,
        authenticating: false,
        displayError: false,
        targetCloud: '',
        lastUsername: undefined,
        error: undefined,
        askForTargetCloud: undefined,
        lastTargetCloud: undefined,
        clientName: undefined,
      };
    },
    computed: {
      isGoogle() {
        return this.clientName?.toLowerCase().indexOf('google') !== -1;
      },
    },
    mounted() {
      this.readRequestFromWindow();
      if (this.lastTargetCloud) {
        this.ownCloud = true;
        this.targetCloud = this.lastTargetCloud;
      }
    },
    methods: {
      readRequestFromWindow() {
        this.lastUsername = window.oauthAuthorizeRequest?.lastUsername;
        this.error = window.oauthAuthorizeRequest?.error;
        this.askForTargetCloud = window.oauthAuthorizeRequest?.askForTargetCloud;
        this.lastTargetCloud = window.oauthAuthorizeRequest?.lastTargetCloud;
        this.clientName = window.oauthAuthorizeRequest?.clientName;
      },
    },
  };
</script>

<style lang="scss">
  .login-oauth-form-broker {
    .login-password {
      display: none;
    }
  }

  .client-name-prompt {
    text-align: center;
    margin-bottom: 25px;
  }
</style>

<template>
  <loading-cover :loading="settings === undefined">
    <div v-if="settings !== undefined" class="container">
      <div class="clearfix left-right-header">
        <div>
          <h5>{{ $t('Your account and MQTT Broker connection.') }}</h5>
          <div class="form-group">
            <div class="btn-group">
              <a class="btn btn-white" target="_blank" href="https://mqtt.org/">mqtt.org</a>
              <a class="btn btn-white" target="_blank" href="https://en.wikipedia.org/wiki/MQTT">MQTT (Wikipedia)</a>
            </div>
          </div>
        </div>
        <div>
          <a v-if="!settings.userEnabled" class="btn btn-green btn-lg" @click="enableMqttBrokerSupport()">
            <button-loading-dots v-if="fetching"></button-loading-dots>
            <span v-else>
              <span class="pe-7s-power"></span>
              {{ $t('Enable') }}
            </span>
          </a>
          <a v-else class="btn btn-orange btn-lg" @click="disableMqttBrokerSupport()">
            <button-loading-dots v-if="fetching"></button-loading-dots>
            <span v-else>
              <span class="pe-7s-power"></span>
              {{ $t('Disable') }}
            </span>
          </a>
        </div>
      </div>

      <div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 mqtt-settings">
        <div v-if="settings.userEnabled" class="panel panel-green">
          <div class="panel-body">
            <div v-if="settings.host">
              <input v-model="settings.host" type="text" readonly />
              <label>{{ $t('Host') }}</label>
              <input v-model="settings.protocol" type="text" readonly />
              <label>{{ $t('Protocol') }}</label>
              <input v-model="settings.port" type="text" readonly />
              <label>{{ $t('Port') }}</label>
              <input type="text" :value="settings.tls ? $t('Enabled') : $t('Disabled')" readonly />
              <label>{{ $t('TLS') }}</label>
            </div>
            <div v-if="settings.integratedAuth">
              <div class="form-group">
                <input v-model="settings.username" type="text" readonly />
                <label>{{ $t('MQTT Broker Username') }}</label>
              </div>
              <div>
                <div v-if="generatedPassword" class="alert alert-warning">
                  <h4>{{ $t('MQTT Broker password') }}</h4>
                  <div class="flex-left-full-width form-group">
                    <pre><code>{{ generatedPassword }}</code></pre>
                    <copy-button :text="generatedPassword"></copy-button>
                  </div>
                  <p>{{ $t('The MQTT Broker password is visible now only. Make sure to save it in a safe place. You will not be able to see it again.') }}</p>
                </div>
                <a class="btn btn-white" @click="generatingPassword = true">{{ $t('Generate new password') }}</a>
              </div>
              <label>{{ $t('MQTT Broker password') }}</label>
            </div>
            <div v-else-if="settings.hasLocalCredentials" class="alert alert-info">
              {{ $t('In order to get the MQTT Broker credentials, concact the administator of this SUPLA Cloud instance.') }}
            </div>
            <div v-else class="alert alert-warning">
              {{ $t('MQTT Broker username and password is not configured in this SUPLA Cloud instance.') }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <modal-confirm
      v-if="generatingPassword"
      class="modal-warning"
      :header="$t('MQTT Broker password')"
      @confirm="generateMqttPassword()"
      @cancel="generatingPassword = false"
    >
      {{ $t('The new password will replace the existing one. Some of the current integrations may not work anymore. Proceed?') }}
    </modal-confirm>
  </loading-cover>
</template>

<script>
  import {infoNotification, successNotification} from '../../common/notifier';
  import CopyButton from '../../common/copy-button.vue';
  import {api} from '@/api/api.js';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import ButtonLoadingDots from '@/common/gui/loaders/button-loading-dots.vue';

  export default {
    components: {ButtonLoadingDots, ModalConfirm, LoadingCover, CopyButton},
    data() {
      return {
        settings: undefined,
        publishEnabled: false,
        generatingPassword: false,
        generatedPassword: undefined,
        fetching: false,
      };
    },
    mounted() {
      this.fetchSettings();
    },
    methods: {
      fetchSettings() {
        this.fetching = true;
        api.get('settings/mqtt-broker').then(({body: settings}) => {
          this.settings = settings;
          this.fetching = false;
        });
      },
      generateMqttPassword() {
        this.fetching = true;
        api
          .patch(`users/current`, {action: 'change:mqttBrokerPassword', password: this.password})
          .then((response) => (this.generatedPassword = response.headers.get('SUPLA-MQTT-Password')))
          .then(() => {
            this.generatingPassword = false;
            successNotification(this.$t('MQTT Broker password has been changed.'));
            return this.fetchSettings();
          });
      },
      enableMqttBrokerSupport() {
        this.fetching = true;
        return api
          .patch(`users/current`, {action: 'change:mqttBrokerEnabled', enabled: true})
          .then((response) => (this.generatedPassword = response.headers.get('SUPLA-MQTT-Password')))
          .then(() => successNotification(this.$t('Integration with MQTT Broker has been enabled.')))
          .finally(this.fetchSettings);
      },
      disableMqttBrokerSupport() {
        this.fetching = true;
        return api
          .patch(`users/current`, {action: 'change:mqttBrokerEnabled', enabled: false})
          .then(() => infoNotification(this.$t('Integration with MQTT Broker has been disabled.')))
          .finally(this.fetchSettings);
      },
    },
  };
</script>

<style lang="scss">
  @use '../../styles/mixins' as *;
  @use '../../styles/variables' as *;

  .mqtt-state-header {
    font-size: 2em;
    span {
      font-weight: bold;
    }
  }

  .mqtt-settings {
    input[type='text'] {
      background: transparent;
      border: none;
      border-bottom: 1px solid rgba(0, 2, 4, 0.25);
      width: 100%;
      line-height: 36px;
      padding: 0 5px;
      margin-top: 8px;
      font-size: 16px;
    }
    label {
      font-size: 13px;
      font-weight: 400;
      color: rgba(0, 2, 4, 0.6);
      line-height: 26px;
    }
  }
</style>

<template>
  <div v-if="subject.config && (subject.config.alexa || subject.config.googleHome)" class="channel-params-integrations-settings">
    <h4 class="text-center">{{ $t('Integrations settings') }}</h4>
    <div class="btn-group d-flex align-items-center justify-content-center">
      <a v-if="subject.config.alexa" class="btn btn-default" @click="openAlexaSettings()">Alexa<sup>&reg;</sup></a>
      <a v-if="subject.config.googleHome" class="btn btn-default" @click="openGoogleSettings()">Google Home<sup>&reg;</sup></a>
    </div>
    <modal
      v-if="alexaSettings"
      class="modal-450"
      :header="$t('{serviceName} integration', {serviceName: 'Alexa®'})"
      :cancellable="true"
      @confirm="updateAlexaSettings()"
      @cancel="alexaSettings = undefined"
    >
      <dl>
        <dd v-if="subject.ownSubjectType === 'channel'">{{ $t('Channel available for {serviceName}', {serviceName: 'Alexa®'}) }}</dd>
        <dd v-else>{{ $t('Scene available for {serviceName}', {serviceName: 'Alexa®'}) }}</dd>
        <dt class="text-center">
          <toggler v-model="alexaEnabled"></toggler>
        </dt>
      </dl>
    </modal>
    <modal
      v-if="googleSettings"
      class="modal-450"
      :header="$t('{serviceName} integration', {serviceName: 'Google Home®'})"
      :cancellable="true"
      @confirm="updateGoogleSettings()"
      @cancel="googleSettings = undefined"
    >
      <dl>
        <dd v-if="subject.ownSubjectType === 'channel'">{{ $t('Channel available for {serviceName}', {serviceName: 'Google Home®'}) }}</dd>
        <dd v-else>{{ $t('Scene available for {serviceName}', {serviceName: 'Google Home®'}) }}</dd>
        <dt class="text-center">
          <toggler v-model="googleEnabled"></toggler>
        </dt>
      </dl>
      <div v-if="googleEnabled && canSetGoogleUserConfirmation">
        <dl>
          <dd>{{ $t('Action confirmation') }}</dd>
          <dt class="text-center">
            <div class="btn-group d-flex align-items-center justify-content-center">
              <a
                :class="['btn', {'btn-default': googleActionConfirmation !== 'none', 'btn-green': googleActionConfirmation === 'none'}]"
                @click="googleActionConfirmation = 'none'"
              >
                {{ $t('None') }}
              </a>
              <a
                :class="['btn', {'btn-default': googleActionConfirmation !== 'simple', 'btn-green': googleActionConfirmation === 'simple'}]"
                @click="googleActionConfirmation = 'simple'"
              >
                {{ $t('Confirmation') }}
              </a>
              <a
                :class="['btn', {'btn-default': googleActionConfirmation !== 'pin', 'btn-green': googleActionConfirmation === 'pin'}]"
                @click="googleActionConfirmation = 'pin'"
              >
                {{ $t('PIN') }}
              </a>
            </div>
          </dt>
        </dl>
        <transition-expand>
          <div v-if="displayNoConfirmationWarning" class="alert alert-warning mt-3 mb-0">
            {{
              $t(
                'By opting out you are agreeing that your device can be controlled without secondary user verification, potentially leading to an unsecure state (or less secure setting).'
              )
            }}
          </div>
        </transition-expand>
        <div v-if="googleEnabled && googleActionConfirmation === 'pin'">
          <dl>
            <dd>{{ $t('PIN') }}</dd>
            <dt class="text-center">
              <input
                v-if="googleSettings.changingPin"
                v-model="googleSettings.pin"
                v-focus
                v-input-digits-only
                type="text"
                maxlength="8"
                class="form-control text-center pin-input no-spinner d-inline-block"
              />
              <a v-else @click="googleSettings.changingPin = true">{{ $t('change') }}</a>
            </dt>
          </dl>
          <transition-expand>
            <div v-if="googleSettings.pinError" class="alert alert-danger mt-3 mb-0">
              {{
                $t('PIN for Google must have a length between {minLength} and {maxLength} digits.', {
                  minLength: 4,
                  maxLength: 8,
                })
              }}
            </div>
          </transition-expand>
        </div>
      </div>
    </modal>
  </div>
</template>

<script>
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import ActionableSubjectType from '@/common/enums/actionable-subject-type';
  import ChannelFunction from '@/common/enums/channel-function';
  import Modal from '@/common/modal.vue';
  import Toggler from '@/common/gui/toggler.vue';

  export default {
    components: {Toggler, Modal, TransitionExpand},
    props: {
      subject: Object,
    },
    data() {
      return {
        googleSettings: undefined,
        alexaSettings: undefined,
      };
    },
    computed: {
      alexaEnabled: {
        get() {
          return !this.alexaSettings.alexaDisabled;
        },
        set(value) {
          this.alexaSettings.alexaDisabled = !value;
        },
      },
      googleEnabled: {
        get() {
          return !this.googleSettings.googleHomeDisabled;
        },
        set(value) {
          this.googleSettings.googleHomeDisabled = !value;
        },
      },
      googleActionConfirmation: {
        get() {
          if (this.googleSettings.needsUserConfirmation) {
            return 'simple';
          } else if (this.googleSettings.pinSet) {
            return 'pin';
          } else {
            return 'none';
          }
        },
        set(value) {
          if (value === 'simple') {
            this.googleSettings.needsUserConfirmation = true;
            this.googleSettings.pinSet = false;
          } else if (value === 'pin') {
            this.googleSettings.needsUserConfirmation = false;
            this.googleSettings.pinSet = true;
          } else {
            this.googleSettings.needsUserConfirmation = false;
            this.googleSettings.pinSet = false;
          }
          this.googleSettings.dirty = true;
        },
      },
      canSetGoogleUserConfirmation() {
        return 'pinSet' in this.subject.config.googleHome;
      },
      displayNoConfirmationWarning() {
        return (
          this.googleActionConfirmation === 'none' &&
          this.googleSettings.dirty &&
          this.subject.ownSubjectType === ActionableSubjectType.CHANNEL &&
          [ChannelFunction.CONTROLLINGTHEGATE, ChannelFunction.CONTROLLINGTHEGARAGEDOOR].includes(this.subject.functionId)
        );
      },
    },
    methods: {
      openGoogleSettings() {
        this.googleSettings = {
          ...(this.subject.config.googleHome || {}),
          pinError: false,
          changingPin: false,
          dirty: false,
        };
        if (!this.googleSettings.pinSet) {
          this.googleSettings.changingPin = true;
        }
      },
      openAlexaSettings() {
        this.alexaSettings = {...(this.subject.config.alexa || {})};
      },
      updateGoogleSettings() {
        this.googleSettings.pinError = false;
        const pin = this.googleSettings.pin || '';
        if (this.googleSettings.pinSet && this.googleSettings.changingPin && (pin.length < 4 || pin.length > 8)) {
          this.googleSettings.pinError = true;
        } else {
          const settings = {
            googleHomeDisabled: this.googleSettings.googleHomeDisabled,
            needsUserConfirmation: this.googleSettings.needsUserConfirmation,
            pinSet: this.googleSettings.pinSet,
          };
          if (this.googleSettings.pinSet && this.googleSettings.changingPin) {
            settings.pin = this.googleSettings.pin;
          }
          if (!this.googleSettings.pinSet && !this.googleSettings.needsUserConfirmation) {
            settings.pin = null;
          }
          this.subject.config.googleHome = settings;
          this.$emit('change');
          this.googleSettings = undefined;
        }
      },
      updateAlexaSettings() {
        this.subject.config.alexa = {alexaDisabled: this.alexaSettings.alexaDisabled};
        this.$emit('change');
        this.alexaSettings = undefined;
      },
    },
  };
</script>

<style lang="scss" scoped>
  .pin-input {
    max-width: 130px;
  }
</style>

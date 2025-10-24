<template>
  <div>
    <dl>
      <dd>{{ $t('Tilt control type') }}</dd>
      <dt>
        <!-- i18n:['tiltControlType_STANDS_IN_POSITION_WHILE_TILTING', 'tiltControlType_CHANGES_POSITION_WHILE_TILTING'] -->
        <!-- i18n:['tiltControlType_TILTS_ONLY_WHEN_FULLY_CLOSED', 'tiltControlType_UNKNOWN'] -->
        <SimpleDropdown
          v-slot="{value}"
          v-model="channel.config.tiltControlType"
          :options="['STANDS_IN_POSITION_WHILE_TILTING', 'CHANGES_POSITION_WHILE_TILTING', 'TILTS_ONLY_WHEN_FULLY_CLOSED']"
          @input="$emit('change')"
        >
          {{ $t(`tiltControlType_${value}`) }}
        </SimpleDropdown>
      </dt>
    </dl>
    <dl v-if="channel.config.timeSettingAvailable && channel.config.autoCalibrationAvailable" class="wide-label">
      <dd>{{ $t('Automatic calibration') }}</dd>
      <dt class="text-center">
        <toggler v-model="automaticCalibration" @update:model-value="$emit('change')"></toggler>
      </dt>
    </dl>
    <transition-expand>
      <dl v-if="channel.config.timeSettingAvailable && (!channel.config.autoCalibrationAvailable || !automaticCalibration)" class="wide-label">
        <dd>{{ $t('Full opening time') }}</dd>
        <dt>
          <span class="input-group">
            <input
              v-model="channel.config.openingTimeS"
              type="number"
              step="0.1"
              min="0"
              max="600"
              class="form-control text-center"
              @change="$emit('change')"
            />
            <span class="input-group-addon">
              {{ $t('sec.') }}
            </span>
          </span>
        </dt>
        <dd>{{ $t('Full closing time') }}</dd>
        <dt>
          <span class="input-group">
            <input
              v-model="channel.config.closingTimeS"
              type="number"
              step="0.1"
              min="0"
              max="600"
              class="form-control text-center"
              @change="$emit('change')"
            />
            <span class="input-group-addon">
              {{ $t('sec.') }}
            </span>
          </span>
        </dt>
      </dl>
    </transition-expand>
    <dl class="wide-label">
      <dd>{{ $t('Full tilting time') }}</dd>
      <dt>
        <span class="input-group">
          <input v-model="channel.config.tiltingTimeS" type="number" step="0.1" min="0" max="600" class="form-control text-center" @change="$emit('change')" />
          <span class="input-group-addon">
            {{ $t('sec.') }}
          </span>
        </span>
      </dt>
    </dl>
    <dl class="wide-label">
      <dd>{{ $t('0% tilt angle') }}</dd>
      <dt>
        <NumberInput
          v-model="channel.config.tilt0Angle"
          :min="0"
          :max="180"
          suffix="°"
          class="form-control text-center mt-2"
          @update:modelValue="$emit('change')"
        />
      </dt>
      <dd>{{ $t('100% tilt angle') }}</dd>
      <dt>
        <NumberInput
          v-model="channel.config.tilt100Angle"
          :min="0"
          :max="180"
          suffix="°"
          class="form-control text-center mt-2"
          @update:modelValue="$emit('change')"
        />
      </dt>
    </dl>
    <dl v-if="channel.config.motorUpsideDown !== undefined" class="wide-label">
      <dd>{{ $t('Motor upside down') }}</dd>
      <dt class="text-center">
        <toggler v-model="channel.config.motorUpsideDown" @update:model-value="$emit('change')"></toggler>
      </dt>
    </dl>
    <dl v-if="channel.config.buttonsUpsideDown !== undefined" class="wide-label">
      <dd>{{ $t('Buttons upside down') }}</dd>
      <dt class="text-center">
        <toggler v-model="channel.config.buttonsUpsideDown" @update:model-value="$emit('change')"></toggler>
      </dt>
    </dl>
    <dl v-if="channel.config.timeMargin !== undefined" class="wide-label">
      <dd>{{ $t('Additional time margin') }}</dd>
      <dt>
        <ChannelParamsControllingthefacadeblindTimeMargin v-model="channel.config.timeMargin" @input="$emit('change')" />
      </dt>
    </dl>
    <div class="form-group"></div>
    <channel-params-controllingtherollershutter-recalibrate v-if="channel.config.recalibrateAvailable" :channel="channel" />
  </div>
</template>

<script>
  import ChannelParamsControllingtherollershutterRecalibrate from './channel-params-controllingtherollershutter-recalibrate.vue';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import NumberInput from '@/common/number-input.vue';
  import ChannelParamsControllingthefacadeblindTimeMargin from '@/channels/params/channel-params-controllingthefacadeblind-time-margin.vue';
  import SimpleDropdown from '@/common/gui/simple-dropdown.vue';
  import Toggler from '@/common/gui/toggler.vue';

  export default {
    components: {
      Toggler,
      SimpleDropdown,
      ChannelParamsControllingthefacadeblindTimeMargin,
      NumberInput,
      TransitionExpand,
      ChannelParamsControllingtherollershutterRecalibrate,
    },
    props: ['channel', 'sensorFunction'],
    data() {
      return {
        manualOpeningTimes: {openingTimeS: 10, closingTimeS: 10},
      };
    },
    computed: {
      automaticCalibration: {
        get() {
          return this.channel.config.openingTimeS === 0;
        },
        set(value) {
          if (value) {
            this.manualOpeningTimes.openingTimeS = this.channel.config.openingTimeS;
            this.manualOpeningTimes.closingTimeS = this.channel.config.closingTimeS;
            this.channel.config.openingTimeS = 0;
            this.channel.config.closingTimeS = 0;
          } else {
            this.channel.config.openingTimeS = this.manualOpeningTimes.openingTimeS;
            this.channel.config.closingTimeS = this.manualOpeningTimes.closingTimeS;
          }
        },
      },
    },
  };
</script>

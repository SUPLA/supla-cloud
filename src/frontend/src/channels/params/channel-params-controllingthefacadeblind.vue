<template>
    <div>
        <dl>
            <dd>{{ $t('Facade blind type') }}</dd>
            <dt>
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button"
                        data-toggle="dropdown">
                        {{ $t(`facadeBlindType_${channel.config.facadeBlindType}`) }}
                        <span class="caret"></span>
                    </button>
                    <!-- i18n:['facadeBlindType_STANDS_IN_POSITION_WHILE_TILTING', 'facadeBlindType_CHANGES_POSITION_WHILE_TILTING'] -->
                    <!-- i18n:['facadeBlindType_TILTS_ONLY_WHEN_FULLY_CLOSED'] -->
                    <ul class="dropdown-menu">
                        <li v-for="type in ['STANDS_IN_POSITION_WHILE_TILTING', 'CHANGES_POSITION_WHILE_TILTING', 'TILTS_ONLY_WHEN_FULLY_CLOSED']"
                            :key="type">
                            <a @click="channel.config.facadeBlindType = type; $emit('change')"
                                v-show="type !== channel.config.facadeBlindType">
                                {{ $t(`facadeBlindType_${type}`) }}
                            </a>
                        </li>
                    </ul>
                </div>
            </dt>
        </dl>
        <dl v-if="channel.config.timeSettingAvailable && channel.config.autoCalibrationAvailable">
            <dd>{{ $t('Automatic calibration') }}</dd>
            <dt class="text-center">
                <toggler v-model="automaticCalibration"
                    @input="$emit('change')"></toggler>
            </dt>
        </dl>
        <transition-expand>
            <dl v-if="channel.config.timeSettingAvailable && (!channel.config.autoCalibrationAvailable || !automaticCalibration)">
                <dd>{{ $t('Full opening time') }}</dd>
                <dt>
                    <span class="input-group">
                        <input type="number"
                            step="0.1"
                            min="0"
                            max="600"
                            class="form-control text-center"
                            v-model="channel.config.openingTimeS"
                            @change="$emit('change')">
                        <span class="input-group-addon">
                            {{ $t('sec.') }}
                        </span>
                    </span>
                </dt>
                <dd>{{ $t('Full closing time') }}</dd>
                <dt>
                    <span class="input-group">
                        <input type="number"
                            step="0.1"
                            min="0"
                            max="600"
                            class="form-control text-center"
                            v-model="channel.config.closingTimeS"
                            @change="$emit('change')">
                        <span class="input-group-addon">
                            {{ $t('sec.') }}
                        </span>
                    </span>
                </dt>
                <dd>{{ $t('Full tilting time') }}</dd>
                <dt>
                    <span class="input-group">
                        <input type="number"
                            step="0.1"
                            min="0"
                            max="600"
                            class="form-control text-center"
                            v-model="channel.config.tiltingTimeS"
                            @change="$emit('change')">
                        <span class="input-group-addon">
                            {{ $t('sec.') }}
                        </span>
                    </span>
                </dt>
            </dl>
        </transition-expand>
        <dl>
            <dd>{{ $t('Motor upside down') }}</dd>
            <dt class="text-center">
                <toggler v-model="channel.config.motorUpsideDown"
                    @input="$emit('change')"></toggler>
            </dt>
            <dd>{{ $t('Buttons upside down') }}</dd>
            <dt class="text-center">
                <toggler v-model="channel.config.buttonsUpsideDown"
                    @input="$emit('change')"></toggler>
            </dt>
            <dd>{{ $t('Additional time margin') }}</dd>
            <dt>
                <toggler v-model="defaultTimeMargin" :label="$t('Use device default')" @input="$emit('change')"/>
                <toggler v-if="!defaultTimeMargin" v-model="enabledTimeMargin" :label="$t('Enabled')" @input="$emit('change')"
                    class="ml-3"/>
                <VueNumber v-model="channel.config.timeMargin"
                    v-if="enabledTimeMargin"
                    :min="1"
                    :max="100"
                    v-bind="{decimal: '.', precision: 0, separator: ' ', suffix: ' %'}"
                    class="form-control text-center mt-2"
                    @change="$emit('change')"/>
            </dt>
            <dd>{{ $t('0% tilt angle') }}</dd>
            <dt>
                <VueNumber v-model="channel.config.tilt0Angle"
                    :min="0"
                    :max="180"
                    v-bind="{decimal: '.', precision: 0, separator: ' ', suffix: '°'}"
                    class="form-control text-center mt-2"
                    @change="$emit('change')"/>
            </dt>
            <dd>{{ $t('100% tilt angle') }}</dd>
            <dt>
                <VueNumber v-model="channel.config.tilt100Angle"
                    :min="0"
                    :max="180"
                    v-bind="{decimal: '.', precision: 0, separator: ' ', suffix: '°'}"
                    class="form-control text-center mt-2"
                    @change="$emit('change')"/>
            </dt>
        </dl>
        <div class="form-group"></div>
        <channel-params-controllingtherollershutter-recalibrate :channel="channel"
            v-if="channel.config.recalibrateAvailable"/>
    </div>
</template>

<script>
    import ChannelParamsControllingtherollershutterRecalibrate from "./channel-params-controllingtherollershutter-recalibrate";
    import TransitionExpand from "@/common/gui/transition-expand";
    import {component as VueNumber} from '@coders-tm/vue-number-format'

    export default {
        components: {TransitionExpand, ChannelParamsControllingtherollershutterRecalibrate, VueNumber},
        props: ['channel', 'sensorFunction'],
        data() {
            return {
                manualOpeningTimes: {openingTimeS: 10, closingTimeS: 10, tiltingTimeS: 5},
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
                        this.manualOpeningTimes.tiltingTimeS = this.channel.config.tiltingTimeS;
                        this.channel.config.openingTimeS = 0;
                        this.channel.config.closingTimeS = 0;
                        this.channel.config.tiltingTimeS = 0;
                    } else {
                        this.channel.config.openingTimeS = this.manualOpeningTimes.openingTimeS;
                        this.channel.config.closingTimeS = this.manualOpeningTimes.closingTimeS;
                        this.channel.config.tiltingTimeS = this.manualOpeningTimes.tiltingTimeS;
                    }
                }
            },
            defaultTimeMargin: {
                set(v) {
                    this.channel.config.timeMargin = v ? -1 : 0;
                },
                get() {
                    return +this.channel.config.timeMargin === -1;
                }
            },
            enabledTimeMargin: {
                set(v) {
                    this.channel.config.timeMargin = v ? 1 : 0;
                },
                get() {
                    return this.channel.config.timeMargin > 0;
                }
            },
        },
    };
</script>
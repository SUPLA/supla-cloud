<template>
    <div>
        <dl>
            <dd>{{ $t('Tilt control type') }}</dd>
            <dt>
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button"
                        data-toggle="dropdown">
                        {{ $t(`tiltControlType_${channel.config.tiltControlType}`) }}
                        <span class="caret"></span>
                    </button>
                    <!-- i18n:['tiltControlType_STANDS_IN_POSITION_WHILE_TILTING', 'tiltControlType_CHANGES_POSITION_WHILE_TILTING'] -->
                    <!-- i18n:['tiltControlType_TILTS_ONLY_WHEN_FULLY_CLOSED', 'tiltControlType_UNKNOWN'] -->
                    <ul class="dropdown-menu">
                        <li v-for="type in ['STANDS_IN_POSITION_WHILE_TILTING', 'CHANGES_POSITION_WHILE_TILTING', 'TILTS_ONLY_WHEN_FULLY_CLOSED']"
                            :key="type">
                            <a @click="channel.config.tiltControlType = type; $emit('change')"
                                v-show="type !== channel.config.tiltControlType">
                                {{ $t(`tiltControlType_${type}`) }}
                            </a>
                        </li>
                    </ul>
                </div>
            </dt>
        </dl>
        <dl v-if="channel.config.timeSettingAvailable && channel.config.autoCalibrationAvailable" class="wide-label">
            <dd>{{ $t('Automatic calibration') }}</dd>
            <dt class="text-center">
                <toggler v-model="automaticCalibration"
                    @input="$emit('change')"></toggler>
            </dt>
        </dl>
        <transition-expand>
            <dl v-if="channel.config.timeSettingAvailable && (!channel.config.autoCalibrationAvailable || !automaticCalibration)"
                class="wide-label">
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
            </dl>
        </transition-expand>
        <dl class="wide-label">
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
        <dl class="wide-label">
            <dd>{{ $t('0% tilt angle') }}</dd>
            <dt>
                <NumberInput v-model="channel.config.tilt0Angle"
                    :min="0"
                    :max="180"
                    suffix="°"
                    class="form-control text-center mt-2"
                    @input="$emit('change')"/>
            </dt>
            <dd>{{ $t('100% tilt angle') }}</dd>
            <dt>
                <NumberInput v-model="channel.config.tilt100Angle"
                    :min="0"
                    :max="180"
                    suffix="°"
                    class="form-control text-center mt-2"
                    @input="$emit('change')"/>
            </dt>
        </dl>
        <dl v-if="channel.config.motorUpsideDown !== undefined" class="wide-label">
            <dd>{{ $t('Motor upside down') }}</dd>
            <dt class="text-center">
                <toggler v-model="channel.config.motorUpsideDown"
                    @input="$emit('change')"></toggler>
            </dt>
        </dl>
        <dl v-if="channel.config.buttonsUpsideDown !== undefined" class="wide-label">
            <dd>{{ $t('Buttons upside down') }}</dd>
            <dt class="text-center">
                <toggler v-model="channel.config.buttonsUpsideDown"
                    @input="$emit('change')"></toggler>
            </dt>
        </dl>
        <dl v-if="channel.config.timeMargin !== undefined" class="wide-label">
            <dd>{{ $t('Additional time margin') }}</dd>
            <dt>
                <ChannelParamsControllingthefacadeblindTimeMargin v-model="channel.config.timeMargin" @input="$emit('change')"/>
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
    import NumberInput from "@/common/number-input.vue";
    import ChannelParamsControllingthefacadeblindTimeMargin
        from "@/channels/params/channel-params-controllingthefacadeblind-time-margin.vue";

    export default {
        components: {
            ChannelParamsControllingthefacadeblindTimeMargin,
            NumberInput,
            TransitionExpand, ChannelParamsControllingtherollershutterRecalibrate
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
                }
            },
        },
    };
</script>

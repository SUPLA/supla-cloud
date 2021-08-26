<template>
    <div>
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
                            max="300"
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
                            max="300"
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
        <dl>
            <dd v-tooltip="$t('Shut the roller until it touches the bottom and type the percentage of closing in. This is not required but will improve the visualization of the roller state in the mobile application when provided.')">
                {{ $t('Bottom position') }}
                <i class="pe-7s-help1"></i>
            </dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="1"
                        min="0"
                        max="100"
                        class="form-control text-center"
                        v-model="channel.config.bottomPosition"
                        @change="$emit('change')">
                    <span class="input-group-addon">%</span>
                </span>
            </dt>
        </dl>
        <dl>
            <dd>{{ $t('Opening sensor') }}</dd>
            <dt>
                <channels-id-dropdown :params="channelsDropdownFilter"
                    v-model="channel.config.openingSensorChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
        <div class="form-group"></div>
        <channel-params-controllingtherollershutter-recalibrate :channel="channel"
            v-if="channel.config.recalibrateAvailable"></channel-params-controllingtherollershutter-recalibrate>
    </div>
</template>

<script>
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import ChannelParamsControllingtherollershutterRecalibrate from "./channel-params-controllingtherollershutter-recalibrate";
    import TransitionExpand from "@/common/gui/transition-expand";

    export default {
        components: {TransitionExpand, ChannelParamsControllingtherollershutterRecalibrate, ChannelsIdDropdown},
        props: ['channel', 'sensorFunction'],
        data() {
            return {
                manualOpeningTimes: {openingTimeS: 10, closingTimeS: 10},
            };
        },
        computed: {
            channelsDropdownFilter() {
                return 'function=' + (this.sensorFunction || 'OPENINGSENSOR_ROLLERSHUTTER');
            },
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

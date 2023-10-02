<template>
    <div>
        <dl>
            <dd>{{ $t('Main thermometer') }}</dd>
            <dt>
                <channels-id-dropdown :params="`function=THERMOMETER,HUMIDITYANDTEMPERATURE&deviceIds=${channel.iodeviceId}`"
                    v-model="channel.config.mainThermometerChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
            <dd>{{ $t('Aux thermometer') }}</dd>
            <dt>
                <channels-id-dropdown :params="`function=THERMOMETER,HUMIDITYANDTEMPERATURE&deviceIds=${channel.iodeviceId}`"
                    v-model="channel.config.auxThermometerChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
        <transition-expand>
            <dl v-if="channel.config.auxThermometerChannelId">
                <dd>{{ $t('Aux thermometer type') }}</dd>
                <dt>
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button" data-toggle="dropdown">
                            {{ $t(`auxThermometerType_${channel.config.auxThermometerType}`) }}
                            <span class="caret"></span>
                        </button>
                        <!-- i18n:['auxThermometerType_NOT_SET', 'auxThermometerType_DISABLED', 'auxThermometerType_FLOOR'] -->
                        <!-- i18n:['auxThermometerType_WATER', 'auxThermometerType_GENERIC_HEATER', 'auxThermometerType_GENERIC_COOLER'] -->
                        <ul class="dropdown-menu">
                            <li v-for="type in ['DISABLED', 'FLOOR', 'WATER', 'GENERIC_HEATER', 'GENERIC_COOLER']" :key="type">
                                <a @click="channel.config.auxThermometerType = type; $emit('change')"
                                    v-show="type !== channel.config.auxThermometerType">
                                    {{ $t(`auxThermometerType_${type}`) }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </dt>
            </dl>
        </transition-expand>
        <dl>
            <dd>{{ $t('Binary sensor') }}</dd>
            <dt>
                <channels-id-dropdown :params="`function=MAILSENSOR&deviceIds=${channel.iodeviceId}`"
                    v-model="channel.config.binarySensorChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
        <dl>
            <dd>{{ $t('Enable anti freeze and overheat protection') }}</dd>
            <dt class="text-center">
                <toggler v-model="channel.config.antiFreezeAndOverheatProtectionEnabled" @input="$emit('change')"/>
            </dt>
            <dd>{{ $t('Temperature setpoint change switches to manual mode') }}</dd>
            <dt class="text-center">
                <toggler v-model="channel.config.temperatureSetpointChangeSwitchesToManualMode" @input="$emit('change')"/>
            </dt>
        </dl>
        <dl v-if="channel.config.availableAlgorithms.length > 1">
            <dd>{{ $t('Algorithm') }}</dd>
            <dt>
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button" data-toggle="dropdown">
                        {{ $t(`thermostatAlgorithm_${channel.config.usedAlgorithm}`) }}
                        <span class="caret"></span>
                    </button>
                    <!-- i18n:['thermostatAlgorithm_ON_OFF_SETPOINT_MIDDLE', 'thermostatAlgorithm_ON_OFF_SETPOINT_AT_MOST'] -->
                    <ul class="dropdown-menu">
                        <li v-for="type in channel.config.availableAlgorithms" :key="type">
                            <a @click="channel.config.usedAlgorithm = type; $emit('change')"
                                v-show="type !== channel.config.usedAlgorithm">
                                {{ $t(`thermostatAlgorithm_${type}`) }}
                            </a>
                        </li>
                    </ul>
                </div>
            </dt>
        </dl>
        <dl>
            <dd>{{ $t('Minimum time when turned on') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="1"
                        min="0"
                        max="600"
                        class="form-control text-center"
                        v-model="channel.config.minOnTimeS"
                        @change="$emit('change')">
                    <span class="input-group-addon">
                        {{ $t('sec.') }}
                    </span>
                </span>
            </dt>
            <dd>{{ $t('Minimum time when turned off') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="1"
                        min="0"
                        max="600"
                        class="form-control text-center"
                        v-model="channel.config.minOffTimeS"
                        @change="$emit('change')">
                    <span class="input-group-addon">
                        {{ $t('sec.') }}
                    </span>
                </span>
            </dt>
            <dd>{{ $t('Output value on error') }}</dd>
            <dt>
                <input type="number"
                    step="1"
                    min="-100"
                    max="100"
                    class="form-control text-center"
                    v-model="channel.config.outputValueOnError"
                    @change="$emit('change')">
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    export default {
        components: {TransitionExpand, ChannelsIdDropdown},
        props: ['channel'],
        watch: {
            'channel.config.mainThermometerChannelId'() {
                if (this.channel.config.auxThermometerChannelId === this.channel.config.mainThermometerChannelId) {
                    this.channel.config.auxThermometerChannelId = null;
                }
            },
            'channel.config.auxThermometerChannelId'() {
                if (this.channel.config.auxThermometerChannelId === this.channel.config.mainThermometerChannelId) {
                    this.channel.config.mainThermometerChannelId = null;
                }
            },
        }
    };
</script>

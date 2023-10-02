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
            <dd>{{ $t('Enable anti freeze and overheat protection') }}</dd>
            <dt class="text-center">
                <toggler v-model="channel.config.antiFreezeAndOverheatProtectionEnabled" @input="$emit('change')"/>
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

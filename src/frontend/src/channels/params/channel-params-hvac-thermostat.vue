<template>
    <div>
        <dl v-if="channel.function.name === 'HVAC_THERMOSTAT'">
            <dd>{{ $t('Subfunction') }}</dd>
            <dt>
                <!-- i18n:['thermostatSubfunction_HEAT', 'thermostatSubfunction_COOL'] -->
                <div class="btn-group btn-group-flex">
                    <a :class="'btn ' + (channel.config.subfunction == type ? 'btn-green' : 'btn-default')"
                        v-for="type in ['HEAT', 'COOL']"
                        :key="type"
                        @click="channel.config.subfunction = type; $emit('change')">
                        {{ $t(`thermostatSubfunction_${type}`) }}
                    </a>
                </div>
            </dt>
        </dl>
        <a class="d-flex accordion-header" @click="displayGroup('related')">
            <span class="flex-grow-1">{{ $t('Related channels') }}</span>
            <span>
                <fa :icon="group === 'related' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'related'">
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
                        <channels-id-dropdown :params="`function=HOTELCARDSENSOR&deviceIds=${channel.iodeviceId}`"
                            v-model="channel.config.binarySensorChannelId"
                            @input="$emit('change')"></channels-id-dropdown>
                    </dt>
                </dl>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('flags')">
            <span class="flex-grow-1">{{ $t('Flags') }}</span>
            <span>
                <fa :icon="group === 'flags' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'flags'">
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
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('behavior')">
            <span class="flex-grow-1">{{ $t('Behavior') }}</span>
            <span>
                <fa :icon="group === 'behavior' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'behavior'">
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
                </dl>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('temperatures')" v-if="availableTemperatures.length">
            <span class="flex-grow-1">{{ $t('Temperatures') }}</span>
            <span>
                <fa :icon="group === 'temperatures' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'temperatures'">
                <!-- i18n:['thermostatTemperature_freezeProtection','thermostatTemperature_eco','thermostatTemperature_comfort'] -->
                <!-- i18n:['thermostatTemperature_boost','thermostatTemperature_heatProtection','thermostatTemperature_histeresis'] -->
                <!-- i18n:['thermostatTemperature_belowAlarm','thermostatTemperature_aboveAlarm','thermostatTemperature_auxMinSetpoint'] -->
                <!-- i18n:['thermostatTemperature_auxMaxSetpoint'] -->
                <dl>
                    <template v-for="temp in availableTemperatures">
                        <dd :key="`dd${temp.name}`">{{ $t(`thermostatTemperature_${temp.name}`) }}</dd>
                        <dt :key="`dt${temp.name}`">
                            <span class="input-group">
                                <input type="number"
                                    step="0.1"
                                    :min="temp.min"
                                    :max="temp.max"
                                    class="form-control text-center"
                                    v-model="channel.config.temperatures[temp.name]"
                                    @change="$emit('change')">
                                <span class="input-group-addon">&deg;C</span>
                            </span>
                        </dt>
                    </template>
                </dl>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('other')">
            <span class="flex-grow-1">{{ $t('Other') }}</span>
            <span>
                <fa :icon="group === 'other' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'other'">
                <dl>
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
        </transition-expand>
    </div>
</template>

<script>
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    export default {
        components: {TransitionExpand, ChannelsIdDropdown},
        props: ['channel'],
        data() {
            return {
                group: undefined,
            };
        },
        methods: {
            displayGroup(group) {
                if (this.group === group) {
                    this.group = undefined;
                } else {
                    this.group = group;
                }
            },
        },
        computed: {
            availableTemperatures() {
                return Object.keys(this.channel.config.temperatures || {}).map(name => {
                    const constraintName = {histeresis: 'histeresis', auxMinSetpoint: 'aux', auxMaxSetpoint: 'aux'}[name] || 'room';
                    const min = this.channel.config.temperatureConstraints?.[`${constraintName}Min`];
                    const max = this.channel.config.temperatureConstraints?.[`${constraintName}Max`];
                    return {name, min, max};
                })
            },
        },
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

<style lang="scss">
    .accordion-header {
        color: inherit;
        font-size: 1.1em;
        margin: .5em 0;
    }
</style>

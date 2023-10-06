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
                        @click="changeSubfunction(type)">
                        {{ $t(`thermostatSubfunction_${type}`) }}
                    </a>
                </div>
            </dt>
        </dl>
        <a class="d-flex accordion-header" @click="displayGroup('related')">
            <span class="flex-grow-1">{{ $t('Thermometers configuration') }}</span>
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
                            v-model="channel.config.mainThermometerChannelId" :hide-none="true"
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
                    <div v-if="channel.config.auxThermometerChannelId">
                        <dl>
                            <dd>{{ $t('Aux thermometer type') }}</dd>
                            <dt>
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button"
                                        data-toggle="dropdown">
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
                        <transition-expand>
                            <div v-if="channel.config.auxThermometerType !== 'DISABLED'">
                                <dl>
                                    <dd>{{ $t('Enable aux min and max setpoints') }}</dd>
                                    <dt class="text-center">
                                        <toggler v-model="channel.config.auxMinMaxSetpointEnabled" @input="$emit('change')"/>
                                    </dt>
                                </dl>
                                <transition-expand>
                                    <dl v-if="channel.config.auxMinMaxSetpointEnabled">
                                        <template v-for="temp in auxMinMaxTemperatures">
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
                                </transition-expand>
                            </div>
                        </transition-expand>
                    </div>
                </transition-expand>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('freeze')">
            <span class="flex-grow-1">{{ $t('Anti freeze and overheat protection') }}</span>
            <span>
                <fa :icon="group === 'freeze' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'freeze'">
                <dl>
                    <dd>{{ $t('Enabled') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.antiFreezeAndOverheatProtectionEnabled" @input="$emit('change')"/>
                    </dt>
                </dl>
                <transition-expand>
                    <dl v-if="channel.config.antiFreezeAndOverheatProtectionEnabled">
                        <template v-for="temp in freezeHeatProtectionTemperatures">
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
                </transition-expand>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('behavior')">
            <span class="flex-grow-1">{{ $t('Behavior settings') }}</span>
            <span>
                <fa :icon="group === 'behavior' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'behavior'">
                <dl>
                    <dd>{{ $t('Related binary sensor') }}</dd>
                    <dt>
                        <channels-id-dropdown :params="`type=SENSORNO&deviceIds=${channel.iodeviceId}`"
                            v-model="channel.config.binarySensorChannelId"
                            @input="$emit('change')"></channels-id-dropdown>
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
                    <template v-for="temp in histeresisTemperatures">
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
                        <div class="btn-group btn-group-flex">
                            <a :class="'btn ' + (channel.config.outputValueOnError == possibleValue.value ? 'btn-green' : 'btn-default')"
                                v-for="possibleValue in possibleOutputValueOnErrorValues"
                                :key="possibleValue.value"
                                @click="channel.config.outputValueOnError = possibleValue.value; $emit('change')">
                                {{ $t(possibleValue.label) }}
                            </a>
                        </div>
                    </dt>
                    <dd>{{ $t('Temperature setpoint change switches to manual mode') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.temperatureSetpointChangeSwitchesToManualMode" @input="$emit('change')"/>
                    </dt>
                </dl>
            </div>
        </transition-expand>
    </div>
</template>

<script>
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import ChannelFunction from "@/common/enums/channel-function";

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
            changeSubfunction(subfunction) {
                this.channel.config.subfunction = subfunction;
                if (this.channel.config.outputValueOnError) {
                    this.channel.config.outputValueOnError = 0;
                }
                this.$emit('change');
            }
        },
        computed: {
            availableTemperatures() {
                // i18n:['thermostatTemperature_freezeProtection','thermostatTemperature_eco','thermostatTemperature_comfort']
                // i18n:['thermostatTemperature_boost','thermostatTemperature_heatProtection','thermostatTemperature_histeresis']
                // i18n:['thermostatTemperature_belowAlarm','thermostatTemperature_aboveAlarm','thermostatTemperature_auxMinSetpoint']
                // i18n:['thermostatTemperature_auxMaxSetpoint']
                return Object.keys(this.channel.config.temperatures || {}).map(name => {
                    const constraintName = {histeresis: 'histeresis', auxMinSetpoint: 'aux', auxMaxSetpoint: 'aux'}[name] || 'room';
                    const min = this.channel.config.temperatureConstraints?.[`${constraintName}Min`];
                    const max = this.channel.config.temperatureConstraints?.[`${constraintName}Max`];
                    return {name, min, max};
                })
            },
            auxMinMaxTemperatures() {
                return [
                    this.availableTemperatures.find(t => t.name === 'auxMinSetpoint'),
                    this.availableTemperatures.find(t => t.name === 'auxMaxSetpoint'),
                ].filter(a => a);
            },
            freezeHeatProtectionTemperatures() {
                return [
                    this.availableTemperatures.find(t => t.name === 'freezeProtection'),
                    this.availableTemperatures.find(t => t.name === 'heatProtection'),
                ].filter(a => a);
            },
            histeresisTemperatures() {
                return [
                    this.availableTemperatures.find(t => t.name === 'histeresis'),
                ].filter(a => a);
            },
            possibleOutputValueOnErrorValues() {
                const values = [{value: 0, label: 'off'}]; // i18n
                if (this.channel.function.id === ChannelFunction.HVAC_THERMOSTAT_AUTO || this.channel.config.subfunction === 'COOL') {
                    values.push({value: -100, label: 'cool'}); // i18n
                }
                if (this.channel.config.subfunction !== 'COOL') {
                    values.push({value: 100, label: 'heat'}); // i18n
                }
                return values;
            }
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

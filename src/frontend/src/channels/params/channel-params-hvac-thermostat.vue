<template>
    <div>
        <dl v-if="canDisplaySetting('subfunction') && channel.function.name === 'HVAC_THERMOSTAT'">
            <dd>{{ $t('Subfunction') }}</dd>
            <dt>
                <!-- i18n:['thermostatSubfunction_HEAT', 'thermostatSubfunction_COOL'] -->
                <div class="btn-group btn-group-flex">
                    <button type="button"
                        :class="'btn ' + (channel.config.subfunction == type ? 'btn-green' : 'btn-default')"
                        v-for="type in ['HEAT', 'COOL']"
                        :disabled="!canChangeSetting('subfunction')"
                        :key="type"
                        @click="changeSubfunction(type)">
                        {{ $t(`thermostatSubfunction_${type}`) }}
                    </button>
                </div>
            </dt>
        </dl>
        <transition-expand>
            <div class="alert alert-warning mt-2" v-if="!channel.config.mainThermometerChannelId">
                {{ $t('The thermostat will not work if the main thermometer is not set.') }}
            </div>
        </transition-expand>
        <dl v-if="channel.config.masterThermostatAvailable && canDisplaySetting('masterThermostatChannelId')">
            <dd>{{ $t('Master thermostat') }}</dd>
            <dt>
                <channels-id-dropdown :params="`type=HVAC&deviceIds=${channel.iodeviceId}&skipIds=${channel.id}`"
                    :disabled="!canChangeSetting('masterThermostatChannelId')"
                    choose-prompt-i18n="Function disabled"
                    v-model="channel.config.masterThermostatChannelId"
                    @channelChanged="(c) => masterThermostat = c"
                    @input="masterThermostatChannelIdJustChanged = true; $emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
        <transition-expand>
            <div class="alert alert-info my-2"
                v-if="masterThermostatChannelIdJustChanged && channel.config.masterThermostatChannelId">
                {{ $t('Do you want to set related channels like thermometers and sensors to be the same as in the master channel?') }}
                <a class="mx-1 btn btn-xs btn-green" @click="setChannelsFromMasterThermostat()">{{ $t('Yes') }}</a>
                <a class="mx-1 btn btn-xs btn-default" @click="masterThermostatChannelIdJustChanged = false">{{ $t('No') }}</a>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('related')"
            v-if="canDisplayAnySetting('mainThermometerChannelId', 'auxThermometerChannelId', 'auxThermometerType')">
            <span class="flex-grow-1">{{ $t('Thermometers configuration') }}</span>
            <span>
                <fa :icon="group === 'related' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'related'">
                <dl v-if="canDisplaySetting('mainThermometerChannelId')">
                    <dd>{{ $t('Main thermometer') }}</dd>
                    <dt>
                        <channels-id-dropdown :params="`function=THERMOMETER,HUMIDITYANDTEMPERATURE&deviceIds=${channel.iodeviceId}`"
                            :filter="(ch) => ch.id !== channel.config.auxThermometerChannelId"
                            v-model="channel.config.mainThermometerChannelId" :hide-none="true"
                            :disabled="!canChangeSetting('mainThermometerChannelId')"
                            @input="$emit('change')"></channels-id-dropdown>
                        <SameDifferentThanMasterThermostat :channel="channel" :master-thermostat="masterThermostat"
                            setting="mainThermometerChannelId" @change="$emit('change')"/>
                    </dt>
                </dl>
                <dl v-if="canDisplaySetting('auxThermometerChannelId')">
                    <dd>{{ $t('Aux thermometer') }}</dd>
                    <dt>
                        <channels-id-dropdown :params="`function=THERMOMETER,HUMIDITYANDTEMPERATURE&deviceIds=${channel.iodeviceId}`"
                            :filter="(ch) => ch.id !== channel.config.mainThermometerChannelId"
                            v-model="channel.config.auxThermometerChannelId"
                            :disabled="!canChangeSetting('auxThermometerChannelId')"
                            @input="auxThermometerChanged()"></channels-id-dropdown>
                        <SameDifferentThanMasterThermostat :channel="channel" :master-thermostat="masterThermostat"
                            setting="auxThermometerChannelId" @change="$emit('change')"/>
                    </dt>
                </dl>
                <transition-expand>
                    <div v-if="channel.config.auxThermometerChannelId && canDisplaySetting('auxThermometerType')">
                        <dl>
                            <dd>{{ $t('Aux thermometer type') }}</dd>
                            <dt>
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button"
                                        :disabled="!canChangeSetting('auxThermometerType')"
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
                            <div v-if="canDisplaySetting('auxMinMaxSetpointEnabled') && channel.config.auxThermometerType !== 'DISABLED'">
                                <dl class="wide-label">
                                    <dd>
                                        <span v-if="channel.config.auxThermometerType === 'FLOOR'">
                                            {{ $t('Enable floor temperature control') }}
                                        </span>
                                        <span v-else-if="channel.config.auxThermometerType === 'WATER'">
                                            {{ $t('Enable water temperature control') }}
                                        </span>
                                        <span v-else-if="channel.config.auxThermometerType === 'GENERIC_HEATER'">
                                            {{ $t('Enable generic heater temperature control') }}
                                        </span>
                                        <span v-else-if="channel.config.auxThermometerType === 'GENERIC_COOLER'">
                                            {{ $t('Enable generic cooler temperature control') }}
                                        </span>
                                    </dd>
                                    <dt class="text-center">
                                        <toggler v-model="channel.config.auxMinMaxSetpointEnabled" @input="$emit('change')"
                                            :disabled="!canChangeSetting('auxMinMaxSetpointEnabled')"/>
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
                                                        :disabled="!canChangeTemperature(temp.name)"
                                                        v-model="channel.config.temperatures[temp.name]"
                                                        @change="temperatureChanged(temp.name)">
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
                <dl v-for="temp in otherTemperatures" :key="`dd${temp.name}`">
                    <dd>{{ $t(`thermostatTemperature_${temp.name}`) }}</dd>
                    <dt>
                        <span class="input-group">
                            <input type="number"
                                step="0.1"
                                :min="temp.min"
                                :max="temp.max"
                                class="form-control text-center"
                                :disabled="!canChangeTemperature(temp.name)"
                                v-model="channel.config.temperatures[temp.name]"
                                @change="temperatureChanged(temp.name)">
                            <span class="input-group-addon">&deg;C</span>
                        </span>
                    </dt>
                </dl>
                <div v-if="channel.config.temperatureControlType && canDisplaySetting('temperatureControlType')">
                    <dl>
                        <dd>{{ $t('Temperature control type') }}</dd>
                        <dt>
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button"
                                    :disabled="!canChangeSetting('temperatureControlType')"
                                    data-toggle="dropdown">
                                    {{ $t(`temperatureControlType_${channel.config.temperatureControlType}`) }}
                                    <span class="caret"></span>
                                </button>
                                <!-- i18n:['temperatureControlType_ROOM_TEMPERATURE', 'temperatureControlType_AUX_HEATER_COOLER_TEMPERATURE'] -->
                                <ul class="dropdown-menu">
                                    <li v-for="type in ['ROOM_TEMPERATURE', 'AUX_HEATER_COOLER_TEMPERATURE']" :key="type">
                                        <a @click="channel.config.temperatureControlType = type; $emit('change')"
                                            v-show="type !== channel.config.temperatureControlType">
                                            {{ $t(`temperatureControlType_${type}`) }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </dt>
                    </dl>
                </div>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('freeze')"
            v-if="canDisplayAnySetting('antiFreezeAndOverheatProtectionEnabled') || (channel.config.antiFreezeAndOverheatProtectionEnabled && freezeHeatProtectionTemperatures.length)">
            <span class="flex-grow-1" v-if="freezeHeatProtectionTemperatures.length === 2">
                {{ $t('Anti-freeze and overheat protection') }}
            </span>
            <span class="flex-grow-1" v-else-if="freezeHeatProtectionTemperatures[0].name === 'heatProtection'">
                {{ $t('Overheat protection') }}
            </span>
            <span class="flex-grow-1" v-else>{{ $t('Anti-freeze protection') }}</span>
            <span>
                <fa :icon="group === 'freeze' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'freeze'">
                <dl v-if="canDisplaySetting('antiFreezeAndOverheatProtectionEnabled')">
                    <dd>{{ $t('Enabled') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.antiFreezeAndOverheatProtectionEnabled" @input="$emit('change')"
                            :disabled="!canChangeSetting('antiFreezeAndOverheatProtectionEnabled')"/>
                    </dt>
                </dl>
                <transition-expand>
                    <dl v-if="channel.config.antiFreezeAndOverheatProtectionEnabled" class="wide-label">
                        <template v-for="temp in freezeHeatProtectionTemperatures">
                            <dd :key="`dd${temp.name}`">{{ $t(`thermostatTemperature_${temp.name}`) }}</dd>
                            <dt :key="`dt${temp.name}`">
                                <span class="input-group d-flex align-items-center justify-content-end">
                                    <input type="number"
                                        step="0.1"
                                        :min="temp.min"
                                        :max="temp.max"
                                        class="form-control text-center"
                                        :disabled="!canChangeTemperature(temp.name)"
                                        v-model="channel.config.temperatures[temp.name]"
                                        @change="temperatureChanged(temp.name)">
                                    <span class="input-group-addon">&deg;C</span>
                                </span>
                            </dt>
                        </template>
                    </dl>
                </transition-expand>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('behavior')"
            v-if="canDisplayAnySetting('binarySensorChannelId', 'pumpSwitchChannelId', 'heatOrColdSourceSwitchChannelId', 'usedAlgorithm', 'minOnTimeS', 'minOffTimeS', 'outputValueOnError', 'temperatureSetpointChangeSwitchesToManualMode')">
            <span class="flex-grow-1">{{ $t('Behavior settings') }}</span>
            <span>
                <fa :icon="group === 'behavior' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'behavior'">
                <dl v-if="canDisplaySetting('binarySensorChannelId')">
                    <dd>{{ $t('External sensor disabling the thermostat') }}</dd>
                    <dt>
                        <channels-id-dropdown :params="`type=SENSORNO&deviceIds=${channel.iodeviceId}`"
                            :disabled="!canChangeSetting('binarySensorChannelId')"
                            choose-prompt-i18n="Function disabled"
                            v-model="channel.config.binarySensorChannelId"
                            @input="$emit('change')"></channels-id-dropdown>
                        <SameDifferentThanMasterThermostat :channel="channel" :master-thermostat="masterThermostat"
                            setting="binarySensorChannelId" @change="$emit('change')"/>
                    </dt>
                </dl>
                <dl v-if="channel.config.pumpSwitchAvailable && canDisplaySetting('pumpSwitchChannelId')">
                    <dd>{{ $t('Pump switch') }}</dd>
                    <dt>
                        <channels-id-dropdown :params="`function=PUMPSWITCH&deviceIds=${channel.iodeviceId}`"
                            choose-prompt-i18n="Function disabled"
                            :disabled="!canChangeSetting('pumpSwitchChannelId')"
                            v-model="channel.config.pumpSwitchChannelId"
                            @input="$emit('change')"></channels-id-dropdown>
                        <SameDifferentThanMasterThermostat :channel="channel" :master-thermostat="masterThermostat"
                            setting="pumpSwitchChannelId" @change="$emit('change')"/>
                    </dt>
                </dl>
                <dl v-if="channel.config.heatOrColdSourceSwitchAvailable && canDisplaySetting('heatOrColdSourceSwitchChannelId')">
                    <dd>{{ $t('Heat or cold source switch') }}</dd>
                    <dt>
                        <channels-id-dropdown :params="`function=HEATORCOLDSOURCESWITCH&deviceIds=${channel.iodeviceId}`"
                            choose-prompt-i18n="Function disabled"
                            :disabled="!canChangeSetting('heatOrColdSourceSwitchChannelId')"
                            v-model="channel.config.heatOrColdSourceSwitchChannelId"
                            @input="$emit('change')"></channels-id-dropdown>
                        <SameDifferentThanMasterThermostat :channel="channel" :master-thermostat="masterThermostat"
                            setting="heatOrColdSourceSwitchChannelId" @change="$emit('change')"/>
                    </dt>
                </dl>
                <div v-if="channel.config.availableAlgorithms.length > 0">
                    <dl v-if="canDisplaySetting('usedAlgorithm')">
                        <dd>
                            {{ $t('Algorithm') }}
                            <a @click="algorithmHelpShown = !algorithmHelpShown"><i class="pe-7s-help1"></i></a>
                        </dd>
                        <dt>
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button" data-toggle="dropdown"
                                    :disabled="!canChangeSetting('usedAlgorithm')">
                                    {{ $t(`thermostatAlgorithm_${channel.config.usedAlgorithm}`) }}
                                    <span class="caret"></span>
                                </button>
                                <!-- i18n:['thermostatAlgorithm_ON_OFF_SETPOINT_MIDDLE', 'thermostatAlgorithm_ON_OFF_SETPOINT_AT_MOST'] -->
                                <!-- i18n:['thermostatAlgorithm_PID'] -->
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
                    <transition-expand>
                        <div class="well small text-muted p-2 display-newlines" v-if="algorithmHelpShown">
                            {{ $t('thermostatAlgorithm_help') }}
                        </div>
                    </transition-expand>
                </div>
                <dl class="wide-label">
                    <template v-for="temp in histeresisTemperatures">
                        <dd :key="`dd${temp.name}`">{{ $t(`thermostatTemperature_${temp.name}`) }}</dd>
                        <dt :key="`dt${temp.name}`">
                            <span class="input-group">
                                <input type="number"
                                    step="0.1"
                                    :min="temp.min"
                                    :max="temp.max"
                                    class="form-control text-center"
                                    :disabled="!canChangeTemperature(temp.name)"
                                    v-model="channel.config.temperatures[temp.name]"
                                    @change="$emit('change')">
                                <span class="input-group-addon">&deg;C</span>
                            </span>
                        </dt>
                    </template>
                </dl>
                <dl class="wide-label" v-if="canDisplaySetting('minOnTimeS')">
                    <dd>
                        <span v-if="heatAvailable && !coolAvailable">{{ $t('Minimum ON time before heating can be turned off') }}</span>
                        <span v-else-if="!heatAvailable && coolAvailable">
                            {{ $t('Minimum ON time before cooling can be turned off') }}
                        </span>
                        <span v-else>{{ $t('Minimum ON time before heating/cooling can be turned off') }}</span>
                    </dd>
                    <dt>
                        <span class="input-group">
                            <input type="number"
                                step="1"
                                min="0"
                                max="600"
                                class="form-control text-center"
                                v-model="channel.config.minOnTimeS"
                                :disabled="!canChangeSetting('minOnTimeS')"
                                @change="$emit('change')">
                            <span class="input-group-addon">
                                {{ $t('sec.') }}
                            </span>
                        </span>
                    </dt>
                </dl>
                <dl class="wide-label" v-if="canDisplaySetting('minOffTimeS')">
                    <dd>
                        <span v-if="heatAvailable && !coolAvailable">{{ $t('Minimum OFF time before heating can be turned on') }}</span>
                        <span v-else-if="!heatAvailable && coolAvailable">
                            {{ $t('Minimum OFF time before cooling can be turned on') }}
                        </span>
                        <span v-else>{{ $t('Minimum OFF time before heating/cooling can be turned on') }}</span>
                    </dd>
                    <dt>
                        <span class="input-group">
                            <input type="number"
                                step="1"
                                min="0"
                                max="600"
                                class="form-control text-center"
                                v-model="channel.config.minOffTimeS"
                                :disabled="!canChangeSetting('minOffTimeS')"
                                @change="$emit('change')">
                            <span class="input-group-addon">
                                {{ $t('sec.') }}
                            </span>
                        </span>
                    </dt>
                </dl>
                <dl class="wide-label" v-if="canDisplaySetting('outputValueOnError')">
                    <dd>
                        {{ $t('Output value on error') }}
                        <a @click="outputValueHelpShown = !outputValueHelpShown"><i class="pe-7s-help1"></i></a>
                    </dd>
                    <dt>
                        <select v-model="channel.config.outputValueOnError" @change="$emit('change')" class="form-control"
                            :disabled="!canChangeSetting('outputValueOnError')">
                            <option v-for="possibleValue in possibleOutputValueOnErrorValues" :value="possibleValue.value"
                                :key="possibleValue.value">
                                {{ $t(possibleValue.label) }}
                            </option>
                        </select>
                    </dt>
                </dl>
                <transition-expand>
                    <div class="well small text-muted p-2 display-newlines" v-if="outputValueHelpShown">
                        {{ $t('thermostatOutputValue_help') }}
                    </div>
                </transition-expand>
                <dl class="wide-label"
                    v-if="canDisplaySetting('temperatureSetpointChangeSwitchesToManualMode') && !channel.config.masterThermostatChannelId">
                    <dd>{{ $t('Temperature setpoint change switches to manual mode') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.temperatureSetpointChangeSwitchesToManualMode" @input="$emit('change')"
                            :disabled="!canChangeSetting('temperatureSetpointChangeSwitchesToManualMode')"/>
                    </dt>
                </dl>
            </div>
        </transition-expand>
    </div>
</template>

<script>
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import SameDifferentThanMasterThermostat from "@/channels/hvac/same-different-than-master-thermostat.vue";

    export default {
        components: {SameDifferentThanMasterThermostat, TransitionExpand, ChannelsIdDropdown},
        props: ['channel'],
        data() {
            return {
                group: undefined,
                algorithmHelpShown: false,
                outputValueHelpShown: false,
                masterThermostat: undefined,
                masterThermostatChannelIdJustChanged: false,
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
            },
            temperatureChanged(name) {
                const autoOffsetMin = this.channel.config.temperatureConstraints.autoOffsetMin || 0;
                if (this.channel.config.temperatures.auxMaxSetpoint !== '' && this.channel.config.temperatures.auxMinSetpoint !== '') {
                    if (name === 'auxMinSetpoint' && this.channel.config.temperatures.auxMaxSetpoint !== '') {
                        this.channel.config.temperatures.auxMaxSetpoint = Math.max(
                            this.channel.config.temperatures.auxMaxSetpoint,
                            +this.channel.config.temperatures.auxMinSetpoint + autoOffsetMin
                        );
                    } else if (name === 'auxMaxSetpoint') {
                        this.channel.config.temperatures.auxMinSetpoint = Math.min(
                            this.channel.config.temperatures.auxMinSetpoint,
                            +this.channel.config.temperatures.auxMaxSetpoint - autoOffsetMin
                        );
                    }
                }
                if (this.channel.config.temperatures.heatProtection !== '' && this.channel.config.temperatures.freezeProtection !== '') {
                    if (name === 'freezeProtection') {
                        this.channel.config.temperatures.heatProtection = Math.max(
                            this.channel.config.temperatures.heatProtection,
                            +this.channel.config.temperatures.freezeProtection + autoOffsetMin
                        );
                    } else if (name === 'heatProtection') {
                        this.channel.config.temperatures.freezeProtection = Math.min(
                            this.channel.config.temperatures.freezeProtection,
                            +this.channel.config.temperatures.heatProtection - autoOffsetMin
                        );
                    }
                }
                this.$emit('change');
            },
            auxThermometerChanged() {
                const auxType = this.channel.config.auxThermometerType || 'NOT_SET';
                if (this.channel.config.auxThermometerChannelId && auxType === 'NOT_SET') {
                    this.channel.config.auxThermometerType = 'FLOOR';
                } else if (!this.channel.config.auxThermometerChannelId) {
                    this.channel.config.auxThermometerType = 'NOT_SET';
                }
                this.$emit('change');
            },
            canDisplaySetting(settingName) {
                return !this.channel.config.hiddenConfigFields?.includes(settingName);
            },
            canDisplayAnySetting(...settingNames) {
                return !!settingNames.find((s) => this.canDisplaySetting(s));
            },
            canChangeSetting(settingName) {
                return !this.channel.config.readOnlyConfigFields?.includes(settingName);
            },
            canChangeTemperature(temperatureName) {
                return !this.channel.config.readOnlyTemperatureConfigFields?.includes(temperatureName);
            },
            setChannelsFromMasterThermostat() {
                if (this.masterThermostat) {
                    this.channel.config.mainThermometerChannelId = this.masterThermostat.config.mainThermometerChannelId;
                    this.channel.config.auxThermometerChannelId = this.masterThermostat.config.auxThermometerChannelId;
                    this.channel.config.binarySensorChannelId = this.masterThermostat.config.binarySensorChannelId;
                    this.channel.config.pumpSwitchChannelId = this.masterThermostat.config.pumpSwitchChannelId;
                    this.channel.config.heatOrColdSourceSwitchChannelId = this.masterThermostat.config.heatOrColdSourceSwitchChannelId;
                }
                this.masterThermostatChannelIdJustChanged = false;
            },
        },
        computed: {
            defaultTemperatureConstraintName() {
                return this.channel.config?.defaultTemperatureConstraintName || 'room';
            },
            availableTemperatures() {
                // i18n:['thermostatTemperature_freezeProtection','thermostatTemperature_eco','thermostatTemperature_comfort']
                // i18n:['thermostatTemperature_boost','thermostatTemperature_heatProtection','thermostatTemperature_histeresis']
                // i18n:['thermostatTemperature_belowAlarm','thermostatTemperature_aboveAlarm','thermostatTemperature_auxMinSetpoint']
                // i18n:['thermostatTemperature_auxMaxSetpoint']
                return Object.keys(this.channel.config.temperatures || {}).map(name => {
                    const constraintName = {histeresis: 'histeresis', auxMinSetpoint: 'aux', auxMaxSetpoint: 'aux'}[name]
                        || this.defaultTemperatureConstraintName;
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
            otherTemperatures() {
                return [
                    this.availableTemperatures.find(t => t.name === 'boost'),
                    this.availableTemperatures.find(t => t.name === 'eco'),
                    this.availableTemperatures.find(t => t.name === 'comfort'),
                ].filter(a => a);
            },
            freezeHeatProtectionTemperatures() {
                const temps = [];
                if (this.heatAvailable) {
                    temps.push(this.availableTemperatures.find(t => t.name === 'freezeProtection'));
                }
                if (this.coolAvailable) {
                    temps.push(this.availableTemperatures.find(t => t.name === 'heatProtection'));
                }
                return temps.filter(a => a);
            },
            histeresisTemperatures() {
                return [
                    this.availableTemperatures.find(t => t.name === 'histeresis'),
                ].filter(a => a);
            },
            possibleOutputValueOnErrorValues() {
                const values = [{value: 0, label: 'off'}]; // i18n
                if (this.coolAvailable) {
                    values.push({value: -100, label: 'cool'}); // i18n
                }
                if (this.heatAvailable) {
                    values.push({value: 100, label: 'heat'}); // i18n
                }
                return values;
            },
            heatAvailable() {
                return this.channel.config.heatingModeAvailable;
            },
            coolAvailable() {
                return this.channel.config.coolingModeAvailable;
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

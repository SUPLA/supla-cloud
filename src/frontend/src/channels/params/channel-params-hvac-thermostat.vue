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

        <AccordionRoot>
            <AccordionItem title-i18n="Thermometers configuration"
                v-if="canDisplayAnySetting('mainThermometerChannelId', 'auxThermometerChannelId', 'auxThermometerType')">
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
                              <!-- i18n:['auxThermometerType_NOT_SET', 'auxThermometerType_DISABLED', 'auxThermometerType_FLOOR'] -->
                              <!-- i18n:['auxThermometerType_WATER', 'auxThermometerType_GENERIC_HEATER', 'auxThermometerType_GENERIC_COOLER'] -->
                              <SimpleDropdown v-model="channel.config.auxThermometerType" @input="$emit('change')" v-slot="{value}"
                                :disabled="!canChangeSetting('auxThermometerType')"
                                :options="['DISABLED', 'FLOOR', 'WATER', 'GENERIC_HEATER', 'GENERIC_COOLER']">
                                  {{ $t(`auxThermometerType_${value}`) }}
                              </SimpleDropdown>
                            </dt>
                        </dl>
                    </div>
                </transition-expand>
                <transition-expand>
                    <div v-if="['FLOOR', 'WATER', 'GENERIC_HEATER', 'GENERIC_COOLER'].includes(channel.config.auxThermometerType) && canDisplaySetting('auxMinMaxSetpointEnabled') && channel.config.auxThermometerType !== 'DISABLED'">
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
                                <toggler v-model="channel.config.auxMinMaxSetpointEnabled" @update:modelValue="$emit('change')"
                                    :disabled="!canChangeSetting('auxMinMaxSetpointEnabled')"/>
                            </dt>
                        </dl>
                        <transition-expand>
                            <dl v-if="channel.config.auxMinMaxSetpointEnabled">
                                <template v-for="temp in auxMinMaxTemperatures"  :key="`dd${temp.name}`">
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
                                </template>
                            </dl>
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
                          <!-- i18n:['temperatureControlType_ROOM_TEMPERATURE', 'temperatureControlType_AUX_HEATER_COOLER_TEMPERATURE'] -->
                          <SimpleDropdown v-slot="{value}" v-model="channel.config.temperatureControlType" @input="$emit('change')"
                            :disabled="!canChangeSetting('temperatureControlType')"
                            :options="['ROOM_TEMPERATURE', 'AUX_HEATER_COOLER_TEMPERATURE']">
                            {{ $t(`temperatureControlType_${value}`) }}
                          </SimpleDropdown>
                        </dt>
                    </dl>
                </div>
            </AccordionItem>
            <AccordionItem
                v-if="canDisplayAnySetting('antiFreezeAndOverheatProtectionEnabled') || (channel.config.antiFreezeAndOverheatProtectionEnabled && freezeHeatProtectionTemperatures.length)"
                :title-i18n="freezeHeatProtectionTemperatures.length === 2 ? $t('Anti-freeze and overheat protection') : (freezeHeatProtectionTemperatures[0].name === 'heatProtection' ? $t('Overheat protection') : $t('Anti-freeze protection'))">
                <dl v-if="canDisplaySetting('antiFreezeAndOverheatProtectionEnabled')">
                    <dd>{{ $t('Enabled') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.antiFreezeAndOverheatProtectionEnabled" @update:modelValue="$emit('change')"
                            :disabled="!canChangeSetting('antiFreezeAndOverheatProtectionEnabled')"/>
                    </dt>
                </dl>
                <transition-expand>
                    <dl v-if="channel.config.antiFreezeAndOverheatProtectionEnabled" class="wide-label">
                        <template v-for="temp in freezeHeatProtectionTemperatures" :key="`dd${temp.name}`">
                            <dd>{{ $t(`thermostatTemperature_${temp.name}`) }}</dd>
                            <dt>
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
            </AccordionItem>
            <AccordionItem title-i18n="Behavior settings"
                v-if="canDisplayAnySetting('binarySensorChannelId', 'pumpSwitchChannelId', 'heatOrColdSourceSwitchChannelId', 'usedAlgorithm', 'minOnTimeS', 'minOffTimeS', 'outputValueOnError', 'temperatureSetpointChangeSwitchesToManualMode')">
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
                          <!-- i18n:['thermostatAlgorithm_ON_OFF_SETPOINT_MIDDLE', 'thermostatAlgorithm_ON_OFF_SETPOINT_AT_MOST'] -->
                          <!-- i18n:['thermostatAlgorithm_PID'] -->
                          <SimpleDropdown v-slot="{value}" v-model="channel.config.usedAlgorithm" @input="$emit('change')"
                            :disabled="!canChangeSetting('usedAlgorithm')"
                             :options="channel.config.availableAlgorithms">
                            {{ $t(`thermostatAlgorithm_${value}`) }}
                          </SimpleDropdown>
                        </dt>
                    </dl>
                    <transition-expand>
                        <div class="well small text-muted p-2 display-newlines" v-if="algorithmHelpShown">
                            {{ $t('Algorithms available on this device') }}:
                            <ul class="pl-4 m-0 mt-1">
                                <!-- i18n:['thermostatAlgorithmHelp_ON_OFF_SETPOINT_MIDDLE', 'thermostatAlgorithmHelp_ON_OFF_SETPOINT_AT_MOST'] -->
                                <!-- i18n:['thermostatAlgorithmHelp_PID'] -->
                                <li v-for="type in channel.config.availableAlgorithms" :key="type">
                                    <strong>{{ $t(`thermostatAlgorithm_${type}`) }}:</strong>
                                    {{ $t(`thermostatAlgorithmHelp_${type}`) }}
                                </li>
                            </ul>
                        </div>
                    </transition-expand>
                </div>
                <transition-expand>
                    <dl v-if="channel.config.usedAlgorithm !== 'PID'">
                        <template v-for="temp in histeresisTemperatures" :key="`dd${temp.name}`">
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
                                        @change="$emit('change')">
                                    <span class="input-group-addon">&deg;C</span>
                                </span>
                            </dt>
                        </template>
                    </dl>
                </transition-expand>
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
                                max="3600"
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
                                max="3600"
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
                        <toggler v-model="channel.config.temperatureSetpointChangeSwitchesToManualMode" @update:modelValue="$emit('change')"
                            :disabled="!canChangeSetting('temperatureSetpointChangeSwitchesToManualMode')"/>
                    </dt>
                </dl>
            </AccordionItem>
            <AccordionItem title-i18n="Device interface"
                v-if="canDisplayAnySetting('localUILock') && channel.config.localUILockingCapabilities">
                <dl>
                    <dd>{{ $t('Lock type') }}</dd>
                    <dt>
                      <!-- i18n:['localUILock_UNLOCKED', 'localUILock_FULL', 'localUILock_TEMPERATURE'] -->
                      <SimpleDropdown v-slot="{value}" v-model="localUILockMode" @input="$emit('change')"
                                :disabled="!canChangeSetting('localUILock')"
                        :options="localUILockingCapabilities">
                        {{ $t(`localUILock_${value}`) }}
                      </SimpleDropdown>
                    </dt>
                </dl>
                <transition-expand>
                    <div v-if="localUILockMode === 'TEMPERATURE'">
                        <h5 class="text-center">{{ $t('Temperatures that can be set from local UI') }}</h5>
                        <dl>
                            <dd>{{ $t('Minimum') }}</dd>
                            <dt>
                                <input type="number" class="form-control" step="0.1"
                                    @change="$emit('change')"
                                    v-model="channel.config.minAllowedTemperatureSetpointFromLocalUI"
                                    :placeholder="defaultTemperatureConstraintMin"
                                    :max="channel.config.maxAllowedTemperatureSetpointFromLocalUI || defaultTemperatureConstraintMax"
                                    :min="defaultTemperatureConstraintMin">
                            </dt>
                            <dd>{{ $t('Maximum') }}</dd>
                            <dt>
                                <input type="number" class="form-control" step="0.1"
                                    @change="$emit('change')"
                                    v-model="channel.config.maxAllowedTemperatureSetpointFromLocalUI"
                                    :placeholder="defaultTemperatureConstraintMax"
                                    :max="defaultTemperatureConstraintMax"
                                    :min="channel.config.minAllowedTemperatureSetpointFromLocalUI || defaultTemperatureConstraintMin">
                            </dt>
                        </dl>
                    </div>
                </transition-expand>
            </AccordionItem>
        </AccordionRoot>
    </div>
</template>

<script>
  import ChannelsIdDropdown from "@/devices/channels-id-dropdown.vue";
  import TransitionExpand from "@/common/gui/transition-expand.vue";
  import SameDifferentThanMasterThermostat
    from "@/channels/hvac/same-different-than-master-thermostat.vue";
  import AccordionRoot from "@/common/gui/accordion/accordion-root.vue";
  import AccordionItem from "@/common/gui/accordion/accordion-item.vue";
  import Toggler from "@/common/gui/toggler.vue";
  import SimpleDropdown from "@/common/gui/simple-dropdown.vue";

  export default {
        components: {
          SimpleDropdown,
          Toggler,
          AccordionItem, AccordionRoot, SameDifferentThanMasterThermostat, TransitionExpand, ChannelsIdDropdown},
        props: ['channel'],
        data() {
            return {
                algorithmHelpShown: false,
                outputValueHelpShown: false,
                masterThermostat: undefined,
                masterThermostatChannelIdJustChanged: false,
            };
        },
        methods: {
            changeSubfunction(subfunction) {
                this.channel.config.subfunction = subfunction;
                if (this.channel.config.outputValueOnError) {
                    this.channel.config.outputValueOnError = 0;
                }
                this.$emit('change');
            },
            temperatureChanged(name) {
                const autoOffsetMin = this.channel.config.temperatureConstraints.autoOffsetMin || 0;
                if (this.auxMinMaxTemperatures.length === 2) {
                    const currentMin = this.channel.config.temperatures.auxMinSetpoint;
                    const currentMax = this.channel.config.temperatures.auxMaxSetpoint;
                    if (name === 'auxMinSetpoint' && currentMax !== '' && this.canChangeTemperature('auxMaxSetpoint')) {
                        const newMax = Math.max(currentMax, +currentMin + autoOffsetMin);
                        this.channel.config.temperatures.auxMaxSetpoint = Math.min(newMax, this.auxMinMaxTemperatures[1].max);
                    } else if (name === 'auxMaxSetpoint' && currentMin !== '' && this.canChangeTemperature('auxMinSetpoint')) {
                        const newMin = Math.min(currentMin, +currentMax - autoOffsetMin);
                        this.channel.config.temperatures.auxMinSetpoint = Math.max(newMin, this.auxMinMaxTemperatures[0].min);
                    }
                }
                if (this.freezeHeatProtectionTemperatures.length === 2) {
                    const currentFreeze = this.channel.config.temperatures.freezeProtection;
                    const currentHeat = this.channel.config.temperatures.heatProtection;
                    if (name === 'freezeProtection' && currentHeat !== '' && this.canChangeTemperature('heatProtection')) {
                        const newHeat = Math.max(currentHeat, +currentFreeze + autoOffsetMin);
                        this.channel.config.temperatures.heatProtection = Math.min(newHeat, this.freezeHeatProtectionTemperatures[1].max);
                    } else if (name === 'heatProtection' && currentFreeze !== '' && this.canChangeTemperature('freezeProtection')) {
                        const newFreeze = Math.min(currentFreeze, +currentHeat - autoOffsetMin);
                        this.channel.config.temperatures.freezeProtection = Math.max(newFreeze, this.freezeHeatProtectionTemperatures[0].min);
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
                    const settingsToCopy = [
                        'mainThermometerChannelId',
                        'auxThermometerChannelId',
                        'binarySensorChannelId',
                        'pumpSwitchChannelId',
                        'heatOrColdSourceSwitchChannelId',
                    ];
                    settingsToCopy
                        .filter((setting) => this.canDisplaySetting(setting))
                        .filter((setting) => this.canChangeSetting(setting))
                        .forEach((setting) => {
                            this.channel.config[setting] = this.masterThermostat.config[setting];
                        });
                }
                this.masterThermostatChannelIdJustChanged = false;
            },
        },
        computed: {
            defaultTemperatureConstraintName() {
                return this.channel.config?.defaultTemperatureConstraintName || 'room';
            },
            defaultTemperatureConstraintMin() {
                return this.channel.config.temperatureConstraints?.[`${this.defaultTemperatureConstraintName}Min`];
            },
            defaultTemperatureConstraintMax() {
                return this.channel.config.temperatureConstraints?.[`${this.defaultTemperatureConstraintName}Max`];
            },
            availableTemperatures() {
                // i18n:['thermostatTemperature_freezeProtection','thermostatTemperature_eco','thermostatTemperature_comfort']
                // i18n:['thermostatTemperature_boost','thermostatTemperature_heatProtection','thermostatTemperature_histeresis']
                // i18n:['thermostatTemperature_belowAlarm','thermostatTemperature_aboveAlarm','thermostatTemperature_auxMinSetpoint']
                // i18n:['thermostatTemperature_auxMaxSetpoint', 'thermostatTemperature_auxHisteresis']
                return Object.keys(this.channel.config.temperatures || {}).map(name => {
                    const constraintName = {
                        histeresis: 'histeresis',
                        auxHisteresis: 'histeresis',
                        auxMinSetpoint: 'aux',
                        auxMaxSetpoint: 'aux',
                    }[name] || this.defaultTemperatureConstraintName;
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
                const temps = [
                    this.availableTemperatures.find(t => t.name === 'histeresis'),
                ];
                if (this.channel.config.auxThermometerChannelId) {
                    temps.push(this.availableTemperatures.find(t => t.name === 'auxHisteresis'));
                }
                return temps.filter(a => a);
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
            localUILockMode: {
                get() {
                    return this.channel.config.localUILock?.[0] || 'UNLOCKED';
                },
                set(mode) {
                    let setting = [];
                    if (mode !== 'UNLOCKED') {
                        setting = [mode];
                    }
                    this.channel.config.localUILock = setting;
                    this.$emit('change');
                }
            },
            localUILockingCapabilities() {
                return ['UNLOCKED', ...this.channel.config.localUILockingCapabilities];
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

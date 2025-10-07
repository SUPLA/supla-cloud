<template>
    <div>
        <div class="alert alert-warning m-0 mt-3" v-if="channel.config.waitingForConfigInit">
            {{ $t('Configuration not available yet. Waiting for the device to connect.') }}
            <fa icon="circle-notch" spin></fa>
        </div>
        <div v-else>
            <ChannelParamsInvertedLogic v-if="channel.config.invertedLogic !== undefined" :channel="channel" @change="$emit('change')"/>
            <ChannelParamsBinaryFilteringTime v-if="channel.config.filteringTimeMs !== undefined" :channel="channel"
                @change="$emit('change')"/>
            <ChannelParamsBinaryTimeout v-if="channel.config.timeoutS !== undefined" :channel="channel" @change="$emit('change')"/>
            <ChannelParamsBinarySensitivity v-if="channel.config.sensitivity !== undefined" :channel="channel" @change="$emit('change')"/>
            <component :is="additionalChannelParamsComponent"
                :channel="channel"
                @change="$emit('change')"
                @save="$emit('save')"></component>
            <ChannelParamsIntegrationsSettings :subject="channel" @change="$emit('change')"
                v-if="channel.config.googleHome || channel.config.alexa"/>
        </div>
    </div>
</template>

<script>
  import * as changeCase from "change-case";
  import EventBus from "@/common/event-bus";
  import ChannelParamsNone from "./channel-params-none.vue";
  import ChannelParamsControllingthedoorlock from "./channel-params-controllingthedoorlock.vue";
  import ChannelParamsControllingthegatewaylock
    from "./channel-params-controllingthegatewaylock.vue";
  import ChannelParamsControllingthegate from "./channel-params-controllingthegate.vue";
  import ChannelParamsControllingthegaragedoor from "./channel-params-controllingthegaragedoor.vue";
  import ChannelParamsControllingtherollershutter
    from "./channel-params-controllingtherollershutter.vue";
  import ChannelParamsControllingtheroofwindow from "./channel-params-controllingtheroofwindow.vue";
  import ChannelParamsOpeningsensorGate from "./channel-params-openingsensor-gate.vue";
  import ChannelParamsOpeningsensorGateway from "./channel-params-openingsensor-gateway.vue";
  import ChannelParamsOpeningsensorDoor from "./channel-params-openingsensor-door.vue";
  import ChannelParamsOpeningsensorGaragedoor from "./channel-params-openingsensor-garagedoor.vue";
  import ChannelParamsOpeningsensorRollershutter
    from "./channel-params-openingsensor-rollershutter.vue";
  import ChannelParamsOpeningsensorRoofwindow from "./channel-params-openingsensor-roofwindow.vue";
  import ChannelParamsStaircasetimer from "./channel-params-staircasetimer.vue";
  import ChannelParamsThermometer from "./channel-params-thermometer.vue";
  import ChannelParamsHumidity from "./channel-params-humidity.vue";
  import ChannelParamsHumidityandtemperature from "./channel-params-humidityandtemperature.vue";
  import ChannelParamsElectricitymeter from "./channel-params-electricity-meter.vue";
  import ChannelParamsIcElectricitymeter from "./channel-params-impulsecounter.vue";
  import ChannelParamsIcGasmeter from "./channel-params-impulsecounter.vue";
  import ChannelParamsIcWatermeter from "./channel-params-impulsecounter.vue";
  import ChannelParamsIcHeatmeter from "./channel-params-impulsecounter.vue";
  import ChannelParamsGeneralPurposeMeasurement
    from "./channel-params-general-purpose-measurement.vue";
  import ChannelParamsGeneralPurposeMeter from "./channel-params-general-purpose-meter.vue";
  import ChannelParamsDigiglassVertical from "./channel-params-digiglass.vue";
  import ChannelParamsDigiglassHorizontal from "./channel-params-digiglass.vue";
  import ChannelParamsPowerswitch from "./channel-params-powerswitch.vue";
  import ChannelParamsLightswitch from "./channel-params-powerswitch.vue";
  import ChannelParamsHvacThermostat from "./channel-params-hvac-thermostat.vue";
  import ChannelParamsHvacThermostatHeatCool from "./channel-params-hvac-thermostat.vue";
  import ChannelParamsHvacDomesticHotWater from "./channel-params-hvac-thermostat.vue";
  import ChannelParamsIntegrationsSettings
    from "@/channels/params/channel-params-integrations-settings.vue";
  import ChannelParamsControllingthefacadeblind
    from "@/channels/params/channel-params-controllingthefacadeblind.vue";
  import ChannelParamsInvertedLogic from "@/channels/params/channel-params-inverted-logic.vue";
  import ChannelParamsValveopenclose from "@/channels/params/channel-params-valveopenclose.vue";
  import ChannelParamsSepticTank from "@/channels/params/channel-params-septic-tank.vue";
  import ChannelParamsBinaryFilteringTime
    from "@/channels/params/channel-params-binary-filtering-time.vue";
  import ChannelParamsBinaryTimeout from "@/channels/params/channel-params-binary-timeout.vue";
  import ChannelParamsBinarySensitivity
    from "@/channels/params/channel-params-binary-sensitivity.vue";
  import {api} from "@/api/api.js";

  export default {
        props: ['channel'],
        components: {
            ChannelParamsBinarySensitivity,
            ChannelParamsBinaryTimeout,
            ChannelParamsBinaryFilteringTime,
            ChannelParamsInvertedLogic,
            ChannelParamsIntegrationsSettings,
            ChannelParamsControllingthedoorlock,
            ChannelParamsControllingthegaragedoor,
            ChannelParamsNone,
            ChannelParamsControllingthegate,
            ChannelParamsControllingthegatewaylock,
            ChannelParamsOpeningsensorGate,
            ChannelParamsOpeningsensorGateway,
            ChannelParamsOpeningsensorDoor,
            ChannelParamsOpeningsensorRollershutter,
            ChannelParamsOpeningsensorRoofwindow,
            ChannelParamsOpeningsensorGaragedoor,
            ChannelParamsControllingtherollershutter,
            ChannelParamsTerraceAwning: ChannelParamsControllingtherollershutter,
            ChannelParamsProjectorScreen: ChannelParamsControllingtherollershutter,
            ChannelParamsCurtain: ChannelParamsControllingtherollershutter,
            ChannelParamsRollerGarageDoor: ChannelParamsControllingtherollershutter,
            ChannelParamsControllingtheroofwindow,
            ChannelParamsStaircasetimer,
            ChannelParamsThermometer,
            ChannelParamsHumidity,
            ChannelParamsHumidityandtemperature,
            ChannelParamsElectricitymeter,
            ChannelParamsIcElectricitymeter,
            ChannelParamsIcGasmeter,
            ChannelParamsIcWatermeter,
            ChannelParamsIcHeatmeter,
            ChannelParamsGeneralPurposeMeasurement,
            ChannelParamsGeneralPurposeMeter,
            ChannelParamsDigiglassVertical,
            ChannelParamsDigiglassHorizontal,
            ChannelParamsPowerswitch,
            ChannelParamsLightswitch,
            ChannelParamsHvacThermostat,
            ChannelParamsHvacThermostatHeatCool,
            ChannelParamsHvacDomesticHotWater,
            ChannelParamsControllingthefacadeblind,
            ChannelParamsVerticalBlind: ChannelParamsControllingthefacadeblind,
            ChannelParamsValveopenclose,
            ChannelParamsSepticTank,
            ChannelParamsWaterTank: ChannelParamsSepticTank,
            ChannelParamsContainer: ChannelParamsSepticTank,
        },
        computed: {
            additionalChannelParamsComponent() {
                const fncName = changeCase.camelCase(this.channel.function.name);
                let componentName = 'ChannelParams' + changeCase.pascalCase(fncName);
                // console.log(componentName);
                if (this.$options.components[componentName]) {
                    return componentName;
                } else {
                    return 'channel-params-none';
                }
            },
        },
        data() {
            return {
                configWaiterInterval: undefined,
            };
        },
        beforeMount() {
            this.configWaiterInterval = setInterval(() => this.fetchConfigIfWaiting(), 5000);
        },
        beforeUnmount() {
            clearInterval(this.configWaiterInterval);
        },
        methods: {
            fetchConfigIfWaiting() {
                if (this.channel.config.waitingForConfigInit) {
                    api.get(`channels/${this.channel.id}`).then(response => {
                        if (!response.body.config?.waitingForConfigInit) {
                            this.channel.config = response.body.config;
                            EventBus.$emit('channel-updated');
                        }
                    });
                }
            }
        }
    };
</script>

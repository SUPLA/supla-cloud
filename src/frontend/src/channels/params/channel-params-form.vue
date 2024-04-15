<template>
    <div>
        <div class="alert alert-warning m-0 mt-3" v-if="channel.config.waitingForConfigInit">
            {{ $t('Configuration not available yet. Waiting for the device to connect.') }}
            <fa icon="circle-notch" spin></fa>
        </div>
        <div v-else>
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
    import changeCase from "change-case";
    import EventBus from "@/common/event-bus";
    import ChannelParamsNone from "./channel-params-none";
    import ChannelParamsControllingthedoorlock from "./channel-params-controllingthedoorlock";
    import ChannelParamsControllingthegatewaylock from "./channel-params-controllingthegatewaylock";
    import ChannelParamsControllingthegate from "./channel-params-controllingthegate";
    import ChannelParamsControllingthegaragedoor from "./channel-params-controllingthegaragedoor";
    import ChannelParamsControllingtherollershutter from "./channel-params-controllingtherollershutter";
    import ChannelParamsControllingtheroofwindow from "./channel-params-controllingtheroofwindow";
    import ChannelParamsOpeningsensorGate from "./channel-params-openingsensor-gate";
    import ChannelParamsOpeningsensorGateway from "./channel-params-openingsensor-gateway";
    import ChannelParamsOpeningsensorDoor from "./channel-params-openingsensor-door";
    import ChannelParamsOpeningsensorGaragedoor from "./channel-params-openingsensor-garagedoor";
    import ChannelParamsOpeningsensorRollershutter from "./channel-params-openingsensor-rollershutter";
    import ChannelParamsOpeningsensorRoofwindow from "./channel-params-openingsensor-roofwindow";
    import ChannelParamsOpeningsensorWindow from "./channel-params-openingsensor-window";
    import ChannelParamsStaircasetimer from "./channel-params-staircasetimer";
    import ChannelParamsThermometer from "./channel-params-thermometer";
    import ChannelParamsHumidity from "./channel-params-humidity";
    import ChannelParamsHumidityandtemperature from "./channel-params-humidityandtemperature";
    import ChannelParamsMailsensor from "./channel-params-mailsensor";
    import ChannelParamsNoliquidsensor from "./channel-params-noliquidsensor";
    import ChannelParamsElectricitymeter from "./channel-params-electricity-meter";
    import ChannelParamsIcElectricitymeter from "./channel-params-impulsecounter";
    import ChannelParamsIcGasmeter from "./channel-params-impulsecounter";
    import ChannelParamsIcWatermeter from "./channel-params-impulsecounter";
    import ChannelParamsIcHeatmeter from "./channel-params-impulsecounter";
    import ChannelParamsGeneralPurposeMeasurement from "./channel-params-general-purpose-measurement";
    import ChannelParamsGeneralPurposeMeter from "./channel-params-general-purpose-meter.vue";
    import ChannelParamsDigiglassVertical from "./channel-params-digiglass";
    import ChannelParamsDigiglassHorizontal from "./channel-params-digiglass";
    import ChannelParamsPowerswitch from "./channel-params-powerswitch";
    import ChannelParamsLightswitch from "./channel-params-powerswitch";
    import ChannelParamsHvacThermostat from "./channel-params-hvac-thermostat.vue";
    import ChannelParamsHvacThermostatHeatCool from "./channel-params-hvac-thermostat.vue";
    import ChannelParamsHvacDomesticHotWater from "./channel-params-hvac-thermostat.vue";
    import ChannelParamsIntegrationsSettings from "@/channels/params/channel-params-integrations-settings";
    import ChannelParamsControllingthefacadeblind from "@/channels/params/channel-params-controllingthefacadeblind.vue";

    export default {
        props: ['channel'],
        components: {
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
            ChannelParamsOpeningsensorWindow,
            ChannelParamsControllingtherollershutter,
            ChannelParamsControllingtheroofwindow,
            ChannelParamsStaircasetimer,
            ChannelParamsThermometer,
            ChannelParamsHumidity,
            ChannelParamsHumidityandtemperature,
            ChannelParamsMailsensor,
            ChannelParamsNoliquidsensor,
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
        },
        computed: {
            additionalChannelParamsComponent() {
                const fncName = changeCase.camelCase(this.channel.function.name);
                let componentName = 'ChannelParams' + changeCase.upperCaseFirst(fncName);
                // console.log(componentName);
                if (this.$options.components[componentName]) {
                    return changeCase.headerCase(componentName);
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
        beforeDestroy() {
            clearInterval(this.configWaiterInterval);
        },
        methods: {
            fetchConfigIfWaiting() {
                if (this.channel.config.waitingForConfigInit) {
                    this.$http.get(`channels/${this.channel.id}`).then(response => {
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

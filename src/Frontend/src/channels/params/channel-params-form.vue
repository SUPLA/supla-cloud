<template>
    <component :is="additionalChannelParamsComponent"
        :channel="channel"
        @change="$emit('change')"></component>
</template>

<script>
    import changeCase from "change-case";
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
    import ChannelParamsGasmeter from "./channel-params-gas-meter";
    import ChannelParamsWatermeter from "./channel-params-water-meter";
    import ChannelParamsHeatmeter from "./channel-params-heat-meter";
    import ChannelParamsDigiglassVertical from "./channel-params-digiglass";
    import ChannelParamsDigiglassHorizontal from "./channel-params-digiglass";
    import ChannelParamsPowerswitch from "./channel-params-powerswitch";

    export default {
        props: ['channel'],
        components: {
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
            ChannelParamsGasmeter,
            ChannelParamsWatermeter,
            ChannelParamsHeatmeter,
            ChannelParamsDigiglassVertical,
            ChannelParamsDigiglassHorizontal,
            ChannelParamsPowerswitch,
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
            }
        }
    };
</script>

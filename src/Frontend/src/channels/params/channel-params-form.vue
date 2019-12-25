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
    import ChannelParamsOpeningsensorGate from "./channel-params-openingsensor-gate";
    import ChannelParamsOpeningsensorGateway from "./channel-params-openingsensor-gateway";
    import ChannelParamsOpeningsensorDoor from "./channel-params-openingsensor-door";
    import ChannelParamsOpeningsensorGaragedoor from "./channel-params-openingsensor-garagedoor";
    import ChannelParamsOpeningsensorRollershutter from "./channel-params-openingsensor-rollershutter";
    import ChannelParamsOpeningsensorWindow from "./channel-params-openingsensor-window";
    import ChannelParamsStaircasetimer from "./channel-params-staircasetimer";
    import ChannelParamsThermometer from "./channel-params-thermometer";
    import ChannelParamsHumidity from "./channel-params-humidity";
    import ChannelParamsHumidityandtemperature from "./channel-params-humidityandtemperature";
    import ChannelParamsMailsensor from "./channel-params-mailsensor";
    import ChannelParamsNoliquidsensor from "./channel-params-noliquidsensor";
    import ChannelParamsElectricitymeter from "./channel-params-electricity-meter";
    import ChannelParamsIcElectricitymeter from "./channel-params-ic-electricitymeter";
    import ChannelParamsIcGasmeter from "./channel-params-ic-gasmeter";
    import ChannelParamsIcWatermeter from "./channel-params-ic-watermeter";
    import ChannelParamsGeneralPurposeMeasurement from "./channel-params-general-purpose-measurement";
    import ChannelParamsVirtualButtonMonostable from "./channel-params-virtual-button-monostable";
    import ChannelParamsVirtualButtonBistable from "./channel-params-virtual-button-bistable";

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
            ChannelParamsOpeningsensorGaragedoor,
            ChannelParamsOpeningsensorWindow,
            ChannelParamsControllingtherollershutter,
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
            ChannelParamsGeneralPurposeMeasurement,
            ChannelParamsVirtualButtonMonostable,
            ChannelParamsVirtualButtonBistable,
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

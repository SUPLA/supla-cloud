import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";

export const ChannelReactionConditions = {
    [ChannelFunction.HUMIDITYANDTEMPERATURE]: [
        {
            caption: 'Temperature', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'temperature',
            component: ReactionConditionThreshold,
            props: {
                label: 'When the temperature will be', // i18n
                suspendLabel: 'and wait until the temperature will be', // i18n
                unit: '°C',
                field: 'temperature',
            },
        },
        {
            caption: 'Humidity', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'humidity',
            component: ReactionConditionThreshold,
            props: {label: 'When the humidity will be', unit: '%', field: 'humidity'}, // i18n
        },
    ],
    // [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
    //     {caption: 'gate was opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
    //     {caption: 'gate was closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
    // ],
};

ChannelReactionConditions[ChannelFunction.THERMOMETER] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][0]];
ChannelReactionConditions[ChannelFunction.HUMIDITY] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][1]];
// ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GATE] = ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GARAGEDOOR];

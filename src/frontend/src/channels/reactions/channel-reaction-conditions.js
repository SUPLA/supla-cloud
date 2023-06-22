import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";

export const ChannelReactionConditions = {
    [ChannelFunction.HUMIDITYANDTEMPERATURE]: [
        {
            caption: 'Temperature', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'temperature' && (Object.hasOwn(on_change_to, 'gt') || Object.hasOwn(on_change_to, 'lt')),
            component: ReactionConditionThreshold,
            props: {label: 'When the temperature will be', unit: '°C', field: 'temperature'}, // i18n
        },
        {
            caption: 'Humidity', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'humidity' && (Object.hasOwn(on_change_to, 'gt') || Object.hasOwn(on_change_to, 'lt')),
            component: ReactionConditionThreshold,
            props: {label: 'When the humidity will be', unit: '%', field: 'humidity'}, // i18n
        },
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {caption: 'gate was opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'gate was closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
    ],
};

ChannelReactionConditions[ChannelFunction.THERMOMETER] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][0]];
ChannelReactionConditions[ChannelFunction.HUMIDITY] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][1]];

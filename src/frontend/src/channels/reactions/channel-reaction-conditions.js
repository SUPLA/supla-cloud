import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";

export const ChannelReactionConditions = {
    [ChannelFunction.HUMIDITYANDTEMPERATURE]: [
        {
            caption: 'temperature changed', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'temperature' && (Object.hasOwn(on_change_to, 'gt') || Object.hasOwn(on_change_to, 'lt')),
            component: ReactionConditionThreshold,
            props: {unit: '°C', field: 'temperature'},
        },
        {
            caption: 'humidity changed', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'humidity' && (Object.hasOwn(on_change_to, 'gt') || Object.hasOwn(on_change_to, 'lt')),
            component: ReactionConditionThreshold,
            props: {unit: '%', field: 'humidity'},
        },
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {caption: 'gate was opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'gate was closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
    ],
};

ChannelReactionConditions[ChannelFunction.THERMOMETER] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][0]];
ChannelReactionConditions[ChannelFunction.HUMIDITY] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][1]];

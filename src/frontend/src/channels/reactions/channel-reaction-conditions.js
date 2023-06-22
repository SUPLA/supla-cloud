import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionTemperature from "@/channels/reactions/params/reaction-condition-temperature.vue";

export const ChannelReactionConditions = {
    [ChannelFunction.THERMOMETER]: [
        {
            caption: 'temperature changed', id: 'change', // i18n
            // def: ({operator = 'lt', value = 20} = {}) => ({on_change_to: {name: 'temperature', [operator]: value}}),
            test: ({on_change_to = {}}) => Object.hasOwn(on_change_to, 'gt') || Object.hasOwn(on_change_to, 'lt'),
            component: ReactionConditionTemperature,
        },
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {caption: 'gate was opened', id: 'open', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'gate was closed', id: 'close', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
    ],
};

import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionTemperature from "@/channels/reactions/params/reaction-condition-temperature.vue";

export const ChannelReactionConditions = {
    [ChannelFunction.HUMIDITYANDTEMPERATURE]: [
        {
            caption: 'temperature changed', id: 'change', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'temperature' && (Object.hasOwn(on_change_to, 'gt') || Object.hasOwn(on_change_to, 'lt')),
            component: ReactionConditionTemperature,
            props: {unit: '°C', field: 'temperature'},
        },
        {
            caption: 'humidity changed', id: 'hum', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'humidity' && (Object.hasOwn(on_change_to, 'gt') || Object.hasOwn(on_change_to, 'lt')),
            component: ReactionConditionTemperature,
            props: {unit: '%', field: 'humidity'},
        },
    ],
    [ChannelFunction.THERMOMETER]: [
        {
            caption: 'temperature changed', id: 'change', // i18n
            test: ({on_change_to = {}}) => Object.hasOwn(on_change_to, 'gt') || Object.hasOwn(on_change_to, 'lt'),
            component: ReactionConditionTemperature,
            props: {unit: '°C', field: 'temperature'},
        },
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {caption: 'gate was opened', id: 'open', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'gate was closed', id: 'close', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
    ],
};

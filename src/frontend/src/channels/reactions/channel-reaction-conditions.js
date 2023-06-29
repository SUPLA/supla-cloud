import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";
import ReactionConditionButtons from "@/channels/reactions/params/reaction-condition-buttons.vue";

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
            props: {
                label: 'When the humidity will be',
                unit: '%',
                field: 'humidity',
                suspendLabel: 'and wait until the humidity will be', // i18n
            }, // i18n
        },
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {
            test: ({on_change_to = {}}) => on_change_to.eq,
            component: ReactionConditionButtons,
            props: {
                label: 'When the garage door will be', // i18n
                options: [
                    {label: 'opened', trigger: {on_change_to: {eq: 'open'}}}, // i18n
                    {label: 'closed', trigger: {on_change_to: {eq: 'closed'}}}, // i18n
                ]
            },
        },
    ],
    [ChannelFunction.POWERSWITCH]: [
        {
            test: ({on_change_to = {}}) => on_change_to.eq,
            component: ReactionConditionButtons,
            props: {
                label: 'When the device will be', // i18n
                options: [
                    {label: 'turned on', trigger: {on_change_to: {eq: 'on'}}}, // i18n
                    {label: 'turned off', trigger: {on_change_to: {eq: 'off'}}}, // i18n
                ]
            },
        },
    ],
};

function withPropLabel(def, label) {
    const newDef = {...def};
    newDef.props = {...def.props, label};
    return newDef;
}

ChannelReactionConditions[ChannelFunction.THERMOMETER] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][0]];
ChannelReactionConditions[ChannelFunction.HUMIDITY] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][1]];
ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GATE] = [
    withPropLabel(ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GARAGEDOOR][0], 'When the gate will be') // i18n
];
ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GATEWAY] = [
    withPropLabel(ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GARAGEDOOR][0], 'When the gateway will be') // i18n
];
ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_DOOR] = [
    withPropLabel(ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GARAGEDOOR][0], 'When the door will be') // i18n
];
ChannelReactionConditions[ChannelFunction.LIGHTSWITCH] = ChannelReactionConditions[ChannelFunction.POWERSWITCH];

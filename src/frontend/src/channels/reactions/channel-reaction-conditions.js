import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";
import ReactionConditionButtons from "@/channels/reactions/params/reaction-condition-buttons.vue";
import ReactionConditionElectricitymeter from "@/channels/reactions/params/reaction-condition-electricitymeter.vue";

export const ChannelReactionConditions = {
    [ChannelFunction.HUMIDITYANDTEMPERATURE]: [
        {
            caption: 'Temperature', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'temperature',
            component: ReactionConditionThreshold,
            props: {
                unit: '°C',
                field: 'temperature',
            },
        },
        {
            caption: 'Humidity', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'humidity',
            component: ReactionConditionThreshold,
            props: {
                fieldLabelI18n: 'the humidity', // i18n
                unit: '%',
                field: 'humidity',
            },
        },
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {
            test: ({on_change_to = {}}) => on_change_to.eq,
            component: ReactionConditionButtons,
            props: {
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
                options: [
                    {label: 'turned on', trigger: {on_change_to: {eq: 'on'}}}, // i18n
                    {label: 'turned off', trigger: {on_change_to: {eq: 'off'}}}, // i18n
                ]
            },
        },
    ],
    [ChannelFunction.ELECTRICITYMETER]: [
        {
            test: ({on_change_to = {}}) => on_change_to.name,
            component: ReactionConditionElectricitymeter,
        },
    ]
};

ChannelReactionConditions[ChannelFunction.THERMOMETER] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][0]];
ChannelReactionConditions[ChannelFunction.HUMIDITY] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][1]];
ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GATE] = ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GARAGEDOOR];
ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GATEWAY] = ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GARAGEDOOR];
ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_DOOR] = ChannelReactionConditions[ChannelFunction.OPENINGSENSOR_GARAGEDOOR];
ChannelReactionConditions[ChannelFunction.LIGHTSWITCH] = ChannelReactionConditions[ChannelFunction.POWERSWITCH];

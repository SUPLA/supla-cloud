import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";
import ReactionConditionButtons from "@/channels/reactions/params/reaction-condition-buttons.vue";
import ReactionConditionElectricitymeter from "@/channels/reactions/params/reaction-condition-electricitymeter.vue";
import ReactionConditionDropdown from "@/channels/reactions/params/reaction-condition-dropdown.vue";

export const ChannelReactionConditions = {
    // [ChannelFunction.CONTROLLINGTHEGATEWAYLOCK]: [],
    // [ChannelFunction.CONTROLLINGTHEGATE]: [],
    // [ChannelFunction.CONTROLLINGTHEGARAGEDOOR]: [],
    // [ChannelFunction.CONTROLLINGTHEDOORLOCK]: [],
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
                unit: '%',
                field: 'humidity',
            },
        },
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [{component: ReactionConditionDropdown}],
    [ChannelFunction.OPENINGSENSOR_GATE]: [{component: ReactionConditionDropdown}],
    [ChannelFunction.OPENINGSENSOR_GATEWAY]: [{component: ReactionConditionDropdown}],
    [ChannelFunction.OPENINGSENSOR_DOOR]: [{component: ReactionConditionDropdown}],
    [ChannelFunction.OPENINGSENSOR_ROLLERSHUTTER]: [{component: ReactionConditionDropdown}],
    [ChannelFunction.OPENINGSENSOR_ROOFWINDOW]: [{component: ReactionConditionDropdown}],
    [ChannelFunction.NOLIQUIDSENSOR]: [],
    [ChannelFunction.CONTROLLINGTHEROLLERSHUTTER]: [
        {
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
        },
    ],
    [ChannelFunction.POWERSWITCH]: [
        {
            test: (t) => t.on_change_to?.eq || t.on_change,
            component: ReactionConditionButtons,
            props: {
                options: [
                    {label: 'turned on', trigger: {on_change_to: {eq: 'on'}}}, // i18n
                    {label: 'turned off', trigger: {on_change_to: {eq: 'off'}}}, // i18n
                    {label: 'turned on or off', trigger: {on_change: {}}}, // i18n
                ]
            },
        },
    ],
    [ChannelFunction.DIMMER]: [],
    [ChannelFunction.RGBLIGHTING]: [],
    [ChannelFunction.DIMMERANDRGBLIGHTING]: [],
    [ChannelFunction.DEPTHSENSOR]: [],
    [ChannelFunction.DISTANCESENSOR]: [],
    [ChannelFunction.OPENINGSENSOR_WINDOW]: [],
    [ChannelFunction.MAILSENSOR]: [],
    [ChannelFunction.WINDSENSOR]: [],
    [ChannelFunction.PRESSURESENSOR]: [],
    [ChannelFunction.RAINSENSOR]: [],
    [ChannelFunction.WEIGHTSENSOR]: [],
    [ChannelFunction.WEATHER_STATION]: [],
    [ChannelFunction.ELECTRICITYMETER]: [
        {
            test: ({on_change_to = {}}) => on_change_to.name,
            component: ReactionConditionElectricitymeter,
        },
    ],
    // [ChannelFunction.IC_ELECTRICITYMETER]: [],
    // [ChannelFunction.IC_GASMETER]: [],
    // [ChannelFunction.IC_WATERMETER]: [],
    // [ChannelFunction.IC_HEATMETER]: [],
    // [ChannelFunction.THERMOSTAT]: [],
    // [ChannelFunction.THERMOSTATHEATPOLHOMEPLUS]: [],
    [ChannelFunction.VALVEOPENCLOSE]: [],
    // [ChannelFunction.VALVEPERCENTAGE]: [],
    // [ChannelFunction.GENERAL_PURPOSE_MEASUREMENT]: [],
    // [ChannelFunction.ACTION_TRIGGER]: [],
    // [ChannelFunction.DIGIGLASS_HORIZONTAL]: [],
    // [ChannelFunction.DIGIGLASS_VERTICAL]: [],

};

ChannelReactionConditions[ChannelFunction.THERMOMETER] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][0]];
ChannelReactionConditions[ChannelFunction.HUMIDITY] = [ChannelReactionConditions[ChannelFunction.HUMIDITYANDTEMPERATURE][1]];
ChannelReactionConditions[ChannelFunction.LIGHTSWITCH] = ChannelReactionConditions[ChannelFunction.POWERSWITCH];
ChannelReactionConditions[ChannelFunction.STAIRCASETIMER] = ChannelReactionConditions[ChannelFunction.POWERSWITCH];
ChannelReactionConditions[ChannelFunction.CONTROLLINGTHEROOFWINDOW] = ChannelReactionConditions[ChannelFunction.CONTROLLINGTHEROLLERSHUTTER];

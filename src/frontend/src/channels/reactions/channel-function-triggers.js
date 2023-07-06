import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";
import ReactionConditionElectricitymeter from "@/channels/reactions/params/reaction-condition-electricitymeter.vue";
import {isEqual} from "lodash";

export const ChannelFunctionTriggers = {
    [ChannelFunction.HUMIDITYANDTEMPERATURE]: [
        {
            caption: 'When the temperature reaches a certain value', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'temperature',
            component: ReactionConditionThreshold,
            props: {
                unit: () => '°C',
                field: 'temperature',
                labelI18n: () => 'When the temperature will be', // i18n
                resumeLabelI18n: () => 'and wait until the temperature will be', // i18n
            },
        },
        {
            caption: 'When the temperature changes', // i18n
            def: () => ({on_change: {name: 'temperature'}})
        },
        {
            caption: 'When the humidity reaches a certain level', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'humidity',
            component: ReactionConditionThreshold,
            props: {
                unit: () => '%',
                field: 'humidity',
                labelI18n: () => 'When the humidity will be', // i18n
                resumeLabelI18n: () => 'and wait until the humidity will be', // i18n
            },
        },
        {
            caption: 'When the humidity changes', // i18n
            def: () => ({on_change: {name: 'humidity'}})
        },
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {caption: 'When the garage door will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'When the garage door will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: 'When the garage door will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_GATE]: [
        {caption: 'When the gate will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'When the gate will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: 'When the gate will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_GATEWAY]: [
        {caption: 'When the gateway will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'When the gateway will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: 'When the gateway will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_DOOR]: [
        {caption: 'When the door will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'When the door will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: 'When the door will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_ROLLERSHUTTER]: [
        {caption: 'When the roller shutter will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'When the roller shutter will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: 'When the roller shutter will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_ROOFWINDOW]: [
        {caption: 'When the roof window will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'When the roof window will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: 'When the roof window will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_WINDOW]: [
        {caption: 'When the window will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'When the window will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: 'When the window will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.NOLIQUIDSENSOR]: [
        {caption: 'When a lack of liquid is detected', def: () => ({on_change_to: {eq: 'lo'}})}, // i18n
        {caption: 'When liquid is detected', def: () => ({on_change_to: {eq: 'hi'}})}, // i18n
        {caption: 'When liquid or lack thereof is detected', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.CONTROLLINGTHEROLLERSHUTTER]: [
        {
            caption: 'When the roller shutter reaches a certain position', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => '% of closing', //i18
                labelI18n: () => 'When the roller shutter reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: 'When the roller shutter position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.CONTROLLINGTHEROOFWINDOW]: [
        {
            caption: 'When the roof window reaches a certain position', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => '% of closing',
                labelI18n: () => 'When the roof window reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: 'When the roof window position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.POWERSWITCH]: [
        {caption: 'When the device is turned on', def: () => ({on_change_to: {eq: 'on'}})}, // i18n
        {caption: 'When the device is turned off', def: () => ({on_change_to: {eq: 'off'}})}, // i18n
        {caption: 'When the device is turned on or off', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.DIMMER]: [
        {
            caption: 'When the lighting reaches a certain level of brightness', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => '%',
                labelI18n: () => 'When the brightness will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the brightness level changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.RGBLIGHTING]: [
        {
            caption: 'When the RGB lighting reaches a certain level of brightness', // i18n
            test: (t) => t.on_change_to?.name === 'color_brightness',
            component: ReactionConditionThreshold,
            props: {
                unit: () => '%',
                field: 'color_brightness',
                labelI18n: () => 'When the brightness will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the RGB lighting brightness level changes', // i18n
            def: () => ({on_change: {name: 'color_brightness'}})
        },
        {
            caption: 'When the color changes', // i18n
            def: () => ({on_change: {name: 'color'}})
        },
    ],
    [ChannelFunction.DIMMERANDRGBLIGHTING]: [
        {
            caption: 'When the lighting reaches a certain level of brightness', // i18n
            test: (t) => t.on_change_to?.name === 'brightness',
            component: ReactionConditionThreshold,
            props: {
                unit: () => '%',
                field: 'brightness',
                labelI18n: () => 'When the brightness will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the dimmer brightness changes', // i18n
            def: () => ({on_change: {name: 'brightness'}})
        },
        {
            caption: 'When the RGB lighting reaches a certain level of brightness', // i18n
            test: (t) => t.on_change_to?.name === 'color_brightness',
            component: ReactionConditionThreshold,
            props: {
                unit: () => '%',
                field: 'color_brightness',
                labelI18n: () => 'When the brightness will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the RGB color brightness changes', // i18n
            def: () => ({on_change: {name: 'color_brightness'}})
        },
        {
            caption: 'When the RGB color changes', // i18n
            def: () => ({on_change: {name: 'color'}})
        },
    ],
    [ChannelFunction.DEPTHSENSOR]: [
        {
            caption: 'When the depth is', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => 'm',
                labelI18n: () => 'When the depth is', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the depth changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.DISTANCESENSOR]: [
        {
            caption: 'When a certain distance is reached', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => 'm',
                labelI18n: () => 'When the distance will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the distance changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.MAILSENSOR]: [
        {caption: 'When a new mail comes', def: () => ({on_change_to: {eq: 'hi'}})}, // i18n
        {caption: 'When the mail has been taken', def: () => ({on_change_to: {eq: 'lo'}})}, // i18n
        {caption: 'When the mail comes or is taken', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.WINDSENSOR]: [
        {
            caption: 'When the wind reaches a certain speed', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => 'm/s',
                labelI18n: () => 'When the wind speed will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the wind speed changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.PRESSURESENSOR]: [
        {
            caption: 'When the pressure reaches a certain level', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => 'hPa',
                labelI18n: () => 'When the pressure will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the pressure changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.RAINSENSOR]: [
        {
            caption: 'When there is certain amount of rain', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => 'l/m',
                labelI18n: () => 'When the precipitation amount will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the rain amount changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.WEIGHTSENSOR]: [
        {
            caption: 'When there weight reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => 'g',
                labelI18n: () => 'When the weight will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: 'When the weight changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.ELECTRICITYMETER]: [
        {
            test: ({on_change_to = {}}) => on_change_to.name,
            component: ReactionConditionElectricitymeter,
            props: {
                labelI18n: (fieldName) => {
                    return {
                        voltage_avg: 'When average voltage of all phases will be', // i18n
                        voltage1: 'When the voltage of phase 1 will be', // i18n
                        voltage2: 'When the voltage of phase 2 will be', // i18n
                        voltage3: 'When the voltage of phase 3 will be', // i18n
                        current_sum: 'When summarized current of all phases will be', // i18n
                        current1: 'When the current of phase 1 will be', // i18n
                        current2: 'When the current of phase 2 will be', // i18n
                        current3: 'When the current of phase 3 will be', // i18n
                        power_active_sum: 'When summarized active power of all phases will be', // i18n
                        power_active1: 'When the active power of phase 1 will be', // i18n
                        power_active2: 'When the active power of phase 2 will be', // i18n
                        power_active3: 'When the active power of phase 3 will be', // i18n
                        power_reactive_sum: 'When summarized reactive power of all phases will be', // i18n
                        power_reactive1: 'When the reactive power of phase 1 will be', // i18n
                        power_reactive2: 'When the reactive power of phase 2 will be', // i18n
                        power_reactive3: 'When the reactive power of phase 3 will be', // i18n
                        power_apparent_sum: 'When summarized apparent power of all phases will be', // i18n
                        power_apparent1: 'When the apparent power of phase 1 will be', // i18n
                        power_apparent2: 'When the apparent power of phase 2 will be', // i18n
                        power_apparent3: 'When the apparent power of phase 3 will be', // i18n
                    }[fieldName];
                },
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
                unit: (fieldName) => {
                    if (fieldName.startsWith('voltage')) {
                        return 'V';
                    } else if (fieldName.startsWith('current')) {
                        return 'A';
                    } else if (fieldName.startsWith('power_active')) {
                        return 'W';
                    } else if (fieldName.startsWith('power_reactive')) {
                        return 'var';
                    } else if (fieldName.startsWith('power_apparent')) {
                        return 'VA';
                    }
                },
            },
        },
    ],
    [ChannelFunction.IC_ELECTRICITYMETER]: [
        {
            caption: 'When the electricity meter reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                operators: ['gt', 'ge', 'eq'],
                disableResume: true,
                labelI18n: () => 'When the value will be', // i18n
            },
        },
        {
            caption: 'When the electricity meter value changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.IC_GASMETER]: [
        {
            caption: 'When the gas meter value reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                operators: ['gt', 'ge', 'eq'],
                disableResume: true,
                labelI18n: () => 'When the gas meter value will be', // i18n
            },
        },
        {
            caption: 'When the gas meter value changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.IC_WATERMETER]: [
        {
            caption: 'When the water meter reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                operators: ['gt', 'ge', 'eq'],
                disableResume: true,
                labelI18n: () => 'When the water meter value will be', // i18n
            },
        },
        {
            caption: 'When the water meter value will be', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.IC_HEATMETER]: [
        {
            caption: 'When the heat meter value reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                operators: ['gt', 'ge', 'eq'],
                disableResume: true,
                labelI18n: () => 'When the calculated value will be', // i18n
            },
        },
        {
            caption: 'When the heat meter value changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.VALVEOPENCLOSE]: [
        {caption: 'When the valve is closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: 'When the valve is opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'When the valve is opened or closed', def: () => ({on_change: {}})}, // i18n
        {caption: 'When the valve is manually closed', def: () => ({on_change_to: {eq: 'closed', name: 'manually_closed'}})}, // i18n
        {caption: 'When flooding is detected', def: () => ({on_change_to: {eq: 'hi', name: 'flooding'}})}, // i18n
    ],
};

ChannelFunctionTriggers[ChannelFunction.THERMOMETER] = [
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][0],
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][1],
];
ChannelFunctionTriggers[ChannelFunction.HUMIDITY] = [
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][2],
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][3],
];

ChannelFunctionTriggers[ChannelFunction.LIGHTSWITCH] = ChannelFunctionTriggers[ChannelFunction.POWERSWITCH];
ChannelFunctionTriggers[ChannelFunction.STAIRCASETIMER] = ChannelFunctionTriggers[ChannelFunction.POWERSWITCH];

export function findTriggerDefinition(channelFunction, trigger) {
    return (ChannelFunctionTriggers[channelFunction] || []).find(t => t.test ? t.test(trigger) : isEqual(t.def(), trigger));
}

export function channelFunctionTriggerCaption(channelFunction, trigger, vue) {
    const triggerDef = findTriggerDefinition(channelFunction, trigger);
    if (triggerDef) {
        if ([ReactionConditionThreshold, ReactionConditionElectricitymeter].includes(triggerDef.component)) {
            const onChangeTo = trigger?.on_change_to || {};
            let operator;
            for (const op of ['eq', 'ne', 'lt', 'gt', 'le', 'ge']) {
                if (Object.hasOwn(onChangeTo, op)) {
                    operator = op;
                }
            }
            const operatorLabel = {eq: '=', ne: '≠', le: '≤', lt: '<', ge: '≥', gt: '>'}[operator];
            const unit = triggerDef.props.unit ? triggerDef.props.unit(onChangeTo.name) : '';
            return vue.$t(triggerDef.props.labelI18n(onChangeTo.name)) + ` ${operatorLabel} ${onChangeTo[operator]}${unit}`;
        } else {
            return vue.$t(triggerDef.caption);
        }
    } else {
        return vue.$t('When the condition is met');
    }
}


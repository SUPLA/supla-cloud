import ChannelFunction from "@/common/enums/channel-function";
import ReactionConditionThreshold from "@/channels/reactions/params/reaction-condition-threshold.vue";
import ReactionConditionElectricitymeter from "@/channels/reactions/params/reaction-condition-electricitymeter.vue";
import {isEqual, uniq} from "lodash";
import {measurementUnit} from "@/channels/channel-helpers";
import {i18n} from "@/locale";

export const ChannelFunctionTriggers = {
    [ChannelFunction.HUMIDITYANDTEMPERATURE]: [
        {
            caption: () => 'When the temperature reaches a certain value', // i18n
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
            caption: () => 'When the temperature changes', // i18n
            def: () => ({on_change: {name: 'temperature'}})
        },
        {
            caption: () => 'When the humidity reaches a certain level', // i18n
            test: ({on_change_to = {}}) => on_change_to.name === 'humidity',
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '%',
                field: 'humidity',
                labelI18n: () => 'When the humidity will be', // i18n
                resumeLabelI18n: () => 'and wait until the humidity will be', // i18n
            },
        },
        {
            caption: () => 'When the humidity changes', // i18n
            def: () => ({on_change: {name: 'humidity'}})
        },
        {
            caption: () => 'When the device starts to be powered from battery', // i18n
            def: () => ({on_change_to: {eq: 'on', name: 'battery_powered'}}),
            canBeSetForChannel: (channel) => channel.config.isBatteryPowered,
        },
        {
            caption: () => 'When the device stops to be powered from battery', // i18n
            def: () => ({on_change_to: {eq: 'off', name: 'battery_powered'}}),
            canBeSetForChannel: (channel) => channel.config.isBatteryPowered,
        },
        {
            caption: () => 'When the battery reaches a certain level', // i18n
            canBeSetForChannel: (channel) => channel.config.isBatteryPowered,
            test: ({on_change_to = {}}) => on_change_to.name === 'battery_level',
            component: ReactionConditionThreshold,
            props: {
                unit: () => '%',
                step: () => 1,
                field: 'battery_level',
                labelI18n: () => 'When the battery level will be', // i18n
                resumeLabelI18n: () => 'and wait until the battery level will be', // i18n
            },
        },
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {caption: () => 'When the garage door will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: () => 'When the garage door will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: () => 'When the garage door will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_GATE]: [
        {caption: () => 'When the gate will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: () => 'When the gate will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: () => 'When the gate will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_GATEWAY]: [
        {caption: () => 'When the gateway will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: () => 'When the gateway will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: () => 'When the gateway will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_DOOR]: [
        {caption: () => 'When the door will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: () => 'When the door will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: () => 'When the door will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_ROLLERSHUTTER]: [
        {caption: () => 'When the roller shutter will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: () => 'When the roller shutter will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: () => 'When the roller shutter will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_ROOFWINDOW]: [
        {caption: () => 'When the roof window will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: () => 'When the roof window will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: () => 'When the roof window will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_WINDOW]: [
        {caption: () => 'When the window will be opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: () => 'When the window will be closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: () => 'When the window will be opened or closed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.NOLIQUIDSENSOR]: [
        {caption: () => 'When a lack of liquid is detected', def: () => ({on_change_to: {eq: 'lo'}})}, // i18n
        {caption: () => 'When liquid is detected', def: () => ({on_change_to: {eq: 'hi'}})}, // i18n
        {caption: () => 'When liquid or lack thereof is detected', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.CONTROLLINGTHEROLLERSHUTTER]: [
        {
            caption: () => 'When the roller shutter reaches a certain position', // i18n
            test: (t) => !!t.on_change_to && !t.on_change_to.name,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '% of closing', // i18n
                labelI18n: () => 'When the roller shutter reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: () => 'When the roller shutter calibration failed', // i18n
            test: (t) => t.on_change_to?.name === 'calibration_failed',
            def: () => ({on_change_to: {eq: 'on', name: 'calibration_failed'}}),
            canBeSetForChannel: (channel) => channel.config.autoCalibrationAvailable,
        },
        {
            caption: () => 'When the roller shutter calibration has started', // i18n
            test: (t) => t.on_change_to?.name === 'calibration_in_progress',
            def: () => ({on_change_to: {eq: 'on', name: 'calibration_in_progress'}}),
            canBeSetForChannel: (channel) => channel.config.autoCalibrationAvailable,
        },
        {
            caption: () => 'When the roller shutter calibration has been lost', // i18n
            test: (t) => t.on_change_to?.name === 'calibration_lost',
            def: () => ({on_change_to: {eq: 'on', name: 'calibration_lost'}}),
            canBeSetForChannel: (channel) => channel.config.autoCalibrationAvailable,
        },
        {
            caption: () => 'When the roller shutter motor reported a problem', // i18n
            test: (t) => t.on_change_to?.name === 'motor_problem',
            def: () => ({on_change_to: {eq: 'on', name: 'motor_problem'}}),
            canBeSetForChannel: (channel) => channel.config.autoCalibrationAvailable,
        },
        {
            caption: () => 'When the roller shutter position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.TERRACE_AWNING]: [
        {
            caption: () => 'When the terrace awning reaches a certain position', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '% of extension', // i18n
                labelI18n: () => 'When the terrace awning reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: () => 'When the terrace awning position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.PROJECTOR_SCREEN]: [
        {
            caption: () => 'When the projector screen reaches a certain position', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '% of closing', // i18n
                labelI18n: () => 'When the projector screen reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: () => 'When the projector screen position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.CURTAIN]: [
        {
            caption: () => 'When the curtain reaches a certain position', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '% of closing', // i18n
                labelI18n: () => 'When the curtain reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: () => 'When the curtain position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.ROLLER_GARAGE_DOOR]: [
        {
            caption: () => 'When the roller garage door a certain position', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '% of closing', // i18n
                labelI18n: () => 'When the roller garage door reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: () => 'When the roller garage door position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.CONTROLLINGTHEFACADEBLIND]: [
        {
            caption: () => 'When the facade blind reaches a certain position', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '% of closing', // i18n
                labelI18n: () => 'When the facade blind reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: () => 'When the facade blind position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.VERTICAL_BLIND]: [
        {
            caption: () => 'When the vertical blind reaches a certain position', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '% of closing', // i18n
                labelI18n: () => 'When the vertical blind reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: () => 'When the vertical blind position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.CONTROLLINGTHEROOFWINDOW]: [
        {
            caption: () => 'When the roof window reaches a certain position', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '% of closing', // i18n
                labelI18n: () => 'When the roof window reaches', // i18n
                resumeLabelI18n: () => 'and wait until it reaches', // i18n
            },
        },
        {
            caption: () => 'When the roof window position changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.POWERSWITCH]: [
        {caption: () => 'When the device is turned on', def: () => ({on_change_to: {eq: 'on'}})}, // i18n
        {caption: () => 'When the device is turned off', def: () => ({on_change_to: {eq: 'off'}})}, // i18n
        {caption: () => 'When the device is turned on or off', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.DIMMER]: [
        {
            caption: () => 'When the lighting reaches a certain level of brightness', // i18n
            test: (t) => !!t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                field: 'brightness',
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '%',
                labelI18n: () => 'When the brightness will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the brightness level changes', // i18n
            def: () => ({on_change: {name: 'brightness'}}),
        },
    ],
    [ChannelFunction.RGBLIGHTING]: [
        {
            caption: () => 'When the RGB lighting reaches a certain level of brightness', // i18n
            test: (t) => t.on_change_to?.name === 'color_brightness',
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '%',
                field: 'color_brightness',
                labelI18n: () => 'When the brightness will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the RGB lighting brightness level changes', // i18n
            def: () => ({on_change: {name: 'color_brightness'}})
        },
        {
            caption: () => 'When the color changes', // i18n
            def: () => ({on_change: {name: 'color'}})
        },
    ],
    [ChannelFunction.DIMMERANDRGBLIGHTING]: [
        {
            min: () => 0, max: () => 100, step: () => 1,
            caption: () => 'When the dimmer reaches a certain brightness level', // i18n
            test: (t) => t.on_change_to?.name === 'brightness',
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '%',
                field: 'brightness',
                labelI18n: () => 'When the brightness will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the dimmer brightness level changes', // i18n
            def: () => ({on_change: {name: 'brightness'}})
        },
        {
            caption: () => 'When the RGB lighting reaches a certain level of brightness', // i18n
            test: (t) => t.on_change_to?.name === 'color_brightness',
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '%',
                field: 'color_brightness',
                labelI18n: () => 'When the brightness will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the RGB lighting brightness level changes', // i18n
            def: () => ({on_change: {name: 'color_brightness'}})
        },
        {
            caption: () => 'When the color changes', // i18n
            def: () => ({on_change: {name: 'color'}})
        },
    ],
    [ChannelFunction.DEPTHSENSOR]: [
        {
            caption: () => 'When the depth is', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => 'm',
                labelI18n: () => 'When the depth is', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the depth changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.DISTANCESENSOR]: [
        {
            caption: () => 'When a certain distance is reached', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => 'm',
                labelI18n: () => 'When the distance will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the distance changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.MAILSENSOR]: [
        {caption: () => 'When a new mail comes', def: () => ({on_change_to: {eq: 'hi'}})}, // i18n
        {caption: () => 'When the mail has been taken', def: () => ({on_change_to: {eq: 'lo'}})}, // i18n
        {caption: () => 'When the mail comes or is taken', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.HOTELCARDSENSOR]: [
        {caption: () => 'When the hotel card is put in', def: () => ({on_change_to: {eq: 'hi'}})}, // i18n
        {caption: () => 'When the hotel card has been taken', def: () => ({on_change_to: {eq: 'lo'}})}, // i18n
        {caption: () => 'When the hotel card is put in or has been taken', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.ALARM_ARMAMENT_SENSOR]: [
        {caption: () => 'When the alarm is armed', def: () => ({on_change_to: {eq: 'hi'}})}, // i18n
        {caption: () => 'When the alarm is disarmed', def: () => ({on_change_to: {eq: 'lo'}})}, // i18n
        {caption: () => 'When the alarm is armed or disarmed', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.FLOOD_SENSOR]: [
        {caption: () => 'When a flood is detected', def: () => ({on_change_to: {eq: 'hi'}})}, // i18n
        {caption: () => 'When a flooding state has been cleared', def: () => ({on_change_to: {eq: 'lo'}})}, // i18n
        {caption: () => 'When a flooding state changes', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.WINDSENSOR]: [
        {
            caption: () => 'When the wind reaches a certain speed', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0,
                unit: () => 'm/s',
                labelI18n: () => 'When the wind speed will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the wind speed changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.PRESSURESENSOR]: [
        {
            caption: () => 'When the pressure reaches a certain level', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0,
                unit: () => 'hPa',
                labelI18n: () => 'When the pressure will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the pressure changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.RAINSENSOR]: [
        {
            caption: () => 'When there is certain amount of rain', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0,
                unit: () => 'l/m',
                labelI18n: () => 'When the precipitation amount will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the rain amount changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.WEIGHTSENSOR]: [
        {
            caption: () => 'When there weight reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: () => 'g',
                labelI18n: () => 'When the weight will be', // i18n
                resumeLabelI18n: () => 'and wait until it will be', // i18n
            },
        },
        {
            caption: () => 'When the weight changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.ELECTRICITYMETER]: [
        {
            test: ({on_change_to = {}}) => on_change_to.name,
            component: ReactionConditionElectricitymeter,
            props: {
                labelI18n: (fieldName) => {
                    if (fieldName.startsWith('voltage')) {
                        return 'When the voltage will be'; // i18n
                    } else if (fieldName.startsWith('current')) {
                        return 'When the current will be'; // i18n
                    } else if (fieldName.startsWith('power_active')) {
                        return 'When the active power will be'; // i18n
                    } else if (fieldName.startsWith('power_reactive')) {
                        return 'When the reactive power will be'; // i18n
                    } else if (fieldName.startsWith('power_apparent')) {
                        return 'When the apparent power will be'; //  i18n
                    } else if (fieldName.startsWith('fae_balanced')) {
                        return 'When the balanced forward active energy will be'; // i18n
                    } else if (fieldName.startsWith('rae_balanced')) {
                        return 'When the balanced reverse active energy will be'; // i18n
                    } else if (fieldName.startsWith('fae')) {
                        return 'When the forward active energy will be'; // i18n
                    } else if (fieldName.startsWith('rae')) {
                        return 'When the reverse active energy will be'; // i18n
                    }
                },
                defaultThreshold: (fieldName, subject) => {
                    if (fieldName.startsWith('voltage')) {
                        return 240;
                    } else if (fieldName.startsWith('current')) {
                        return 16;
                    } else if (fieldName.startsWith('fae')) {
                        const energy = (() => {
                            switch (fieldName) {
                                case 'fae1':
                                    return subject.state?.phases?.[0]?.totalForwardActiveEnergy;
                                case 'fae2':
                                    return subject.state?.phases?.[1]?.totalForwardActiveEnergy;
                                case 'fae3':
                                    return subject.state?.phases?.[2]?.totalForwardActiveEnergy;
                                default:
                                    return (subject.config.enabledPhases || [1, 2, 3])
                                        .map(phaseNo => subject.state?.phases?.[phaseNo - 1]?.totalForwardActiveEnergy)
                                        .reduce((e, sum) => +e + sum, 0);
                            }
                        })();
                        return Math.ceil((energy || 0) + 1000);
                    } else if (fieldName.startsWith('rae')) {
                        const energy = (() => {
                            switch (fieldName) {
                                case 'rae1':
                                    return subject.state?.phases?.[0]?.totalReverseActiveEnergy;
                                case 'rae2':
                                    return subject.state?.phases?.[1]?.totalReverseActiveEnergy;
                                case 'rae3':
                                    return subject.state?.phases?.[2]?.totalReverseActiveEnergy;
                                default:
                                    return (subject.config.enabledPhases || [1, 2, 3])
                                        .map(phaseNo => subject.state?.phases?.[phaseNo - 1]?.totalReverseActiveEnergy)
                                        .reduce((e, sum) => +e + sum, 0);
                            }
                        })();
                        return Math.ceil((energy || 0) + 1000);
                    } else {
                        return 1000;
                    }
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
                    } else if (fieldName.startsWith('fae') || fieldName.startsWith('rae')) {
                        return 'kWh';
                    }
                },
            },
        },
    ],
    [ChannelFunction.IC_ELECTRICITYMETER]: [
        {
            caption: () => 'When the electricity meter reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: (fieldName, subject) => measurementUnit(subject),
                operators: ['gt', 'ge', 'eq'],
                field: 'calculated_value',
                disableResume: true,
                labelI18n: () => 'When the electricity meter value will be', // i18n
            },
        },
        {
            caption: () => 'When the electricity meter value changes', // i18n
            def: () => ({on_change: {name: 'calculated_value'}})
        },
    ],
    [ChannelFunction.IC_GASMETER]: [
        {
            caption: () => 'When the gas meter value reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: (fieldName, subject) => measurementUnit(subject),
                field: 'calculated_value',
                operators: ['gt', 'ge', 'eq'],
                disableResume: true,
                labelI18n: () => 'When the gas meter value will be', // i18n
            },
        },
        {
            caption: () => 'When the gas meter value changes', // i18n
            def: () => ({on_change: {name: 'calculated_value'}})
        },
    ],
    [ChannelFunction.IC_WATERMETER]: [
        {
            caption: () => 'When the water meter reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: (fieldName, subject) => measurementUnit(subject),
                field: 'calculated_value',
                operators: ['gt', 'ge', 'eq'],
                disableResume: true,
                labelI18n: () => 'When the water meter value will be', // i18n
            },
        },
        {
            caption: () => 'When the water meter value changes', // i18n
            def: () => ({on_change: {name: 'calculated_value'}})
        },
    ],
    [ChannelFunction.IC_HEATMETER]: [
        {
            caption: () => 'When the heat meter value reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: (fieldName, subject) => measurementUnit(subject),
                field: 'calculated_value',
                operators: ['gt', 'ge', 'eq'],
                disableResume: true,
                labelI18n: () => 'When the heat meter value will be', // i18n
            },
        },
        {
            caption: () => 'When the heat meter value changes', // i18n
            def: () => ({on_change: {name: 'calculated_value'}})
        },
    ],
    [ChannelFunction.VALVEOPENCLOSE]: [
        {caption: () => 'When the valve is closed', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
        {caption: () => 'When the valve is opened', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: () => 'When the valve is opened or closed', def: () => ({on_change: {}})}, // i18n
        {caption: () => 'When the valve is manually closed', def: () => ({on_change_to: {eq: 'closed', name: 'manually_closed'}})}, // i18n
        {caption: () => 'When flooding is detected', def: () => ({on_change_to: {eq: 'hi', name: 'flooding'}})}, // i18n
    ],
    [ChannelFunction.HVAC_DOMESTIC_HOT_WATER]: [
        {caption: () => 'When the thermostat starts heating', def: () => ({on_change_to: {eq: 'on', name: 'is_on'}})}, // i18n
        {caption: () => 'When the thermostat stops heating', def: () => ({on_change_to: {eq: 'off', name: 'is_on'}})}, // i18n
        {caption: () => 'When the thermostat starts or stops heating', def: () => ({on_change: {name: 'is_on'}})}, // i18n
        {
            caption: () => 'When the battery cover is opened', // i18n
            def: () => ({on_change_to: {eq: 'on', name: 'is_battery_cover_open'}}),
            canBeSetForChannel: (channel) => channel.config.isBatteryCoverAvailable,
        },
        {
            caption: () => 'When the device starts to be powered from battery', // i18n
            def: () => ({on_change_to: {eq: 'on', name: 'battery_powered'}}),
            canBeSetForChannel: (channel) => channel.config.isBatteryPowered,
        },
        {
            caption: () => 'When the device stops to be powered from battery', // i18n
            def: () => ({on_change_to: {eq: 'off', name: 'battery_powered'}}),
            canBeSetForChannel: (channel) => channel.config.isBatteryPowered,
        },
        {
            caption: () => 'When the battery reaches a certain level', // i18n
            canBeSetForChannel: (channel) => channel.config.isBatteryPowered,
            test: ({on_change_to = {}}) => on_change_to.name === 'battery_level',
            component: ReactionConditionThreshold,
            props: {
                unit: () => '%',
                step: () => 1,
                field: 'battery_level',
                labelI18n: () => 'When the battery level will be', // i18n
                resumeLabelI18n: () => 'and wait until the battery level will be', // i18n
            },
        },
        {caption: () => 'When the thermometer error has been reported', def: () => ({on_change_to: {eq: 'on', name: 'thermometer_error'}})}, // i18n
        {caption: () => 'When the clock error has been reported', def: () => ({on_change_to: {eq: 'on', name: 'clock_error'}})}, // i18n
    ],
    [ChannelFunction.HVAC_THERMOSTAT]: [
        {caption: () => 'When the thermostat starts heating', def: () => ({on_change_to: {eq: 'on', name: 'heating'}})}, // i18n
        {caption: () => 'When the thermostat starts cooling', def: () => ({on_change_to: {eq: 'on', name: 'cooling'}})}, // i18n
        {caption: () => 'When the thermostat stops heating', def: () => ({on_change_to: {eq: 'off', name: 'heating'}})}, // i18n
        {caption: () => 'When the thermostat stops cooling', def: () => ({on_change_to: {eq: 'off', name: 'cooling'}})}, // i18n
        {
            caption: () => 'When the thermostat starts heating or cooling', // i18n
            def: () => ({on_change_to: {eq: 'on', name: 'heating_or_cooling'}})
        },
        {
            caption: () => 'When the thermostat stops heating or cooling', // i18n
            def: () => ({on_change_to: {eq: 'off', name: 'heating_or_cooling'}})
        },
        {caption: () => 'When the thermostat starts or stops heating', def: () => ({on_change: {name: 'heating'}})}, // i18n
        {caption: () => 'When the thermostat starts or stops cooling', def: () => ({on_change: {name: 'cooling'}})}, // i18n
        {caption: () => 'When the thermostat starts or stops heating or cooling', def: () => ({on_change: {name: 'heating_or_cooling'}})}, // i18n
        {
            caption: () => 'When the battery cover is opened', // i18n
            def: () => ({on_change_to: {eq: 'on', name: 'is_battery_cover_open'}}),
            canBeSetForChannel: (channel) => channel.config.isBatteryCoverAvailable,
        },
        {
            caption: () => 'When the device starts to be powered from battery', // i18n
            def: () => ({on_change_to: {eq: 'on', name: 'battery_powered'}}),
            canBeSetForChannel: (channel) => channel.config.isBatteryPowered,
        },
        {
            caption: () => 'When the device stops to be powered from battery', // i18n
            def: () => ({on_change_to: {eq: 'off', name: 'battery_powered'}}),
            canBeSetForChannel: (channel) => channel.config.isBatteryPowered,
        },
        {
            caption: () => 'When the battery reaches a certain level', // i18n
            canBeSetForChannel: (channel) => channel.config.isBatteryPowered,
            test: ({on_change_to = {}}) => on_change_to.name === 'battery_level',
            component: ReactionConditionThreshold,
            props: {
                unit: () => '%',
                step: () => 1,
                field: 'battery_level',
                labelI18n: () => 'When the battery level will be', // i18n
                resumeLabelI18n: () => 'and wait until the battery level will be', // i18n
            },
        },
        {caption: () => 'When the thermometer error has been reported', def: () => ({on_change_to: {eq: 'on', name: 'thermometer_error'}})}, // i18n
        {caption: () => 'When the clock error has been reported', def: () => ({on_change_to: {eq: 'on', name: 'clock_error'}})}, // i18n
    ],
    [ChannelFunction.GENERAL_PURPOSE_METER]: [
        {
            caption: () => 'When the meter reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: (fieldName, subject) => (subject.config.noSpaceAfterValue ? '' : ' ') + subject.config.unitAfterValue,
                unitBefore: (fieldName, subject) => subject.config.unitBeforeValue + (subject.config.noSpaceBeforeValue ? '' : ' '),
                // field: 'value',
                operators: ['gt', 'ge', 'eq'],
                disableResume: true,
                labelI18n: () => 'When the meter value will be', // i18n
            },
        },
        {
            caption: () => 'When the meter value changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.GENERAL_PURPOSE_MEASUREMENT]: [
        {
            caption: () => 'When the meter reaches a certain value', // i18n
            test: (t) => t.on_change_to,
            component: ReactionConditionThreshold,
            props: {
                unit: (fieldName, subject) => (subject.config.noSpaceAfterValue ? '' : ' ') + subject.config.unitAfterValue,
                unitBefore: (fieldName, subject) => subject.config.unitBeforeValue + (subject.config.noSpaceBeforeValue ? '' : ' '),
                labelI18n: () => 'When the meter value will be', // i18n
                resumeLabelI18n: () => 'and wait until the meter will be', // i18n
            },
        },
        {
            caption: () => 'When the meter value changes', // i18n
            def: () => ({on_change: {}})
        },
    ],
    [ChannelFunction.PUMPSWITCH]: [
        {caption: () => 'When the pump is turned on', def: () => ({on_change_to: {eq: 'on'}})}, // i18n
        {caption: () => 'When the pump is turned off', def: () => ({on_change_to: {eq: 'off'}})}, // i18n
        {caption: () => 'When the pump is turned on or off', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.HEATORCOLDSOURCESWITCH]: [
        {caption: () => 'When the device is turned on', def: () => ({on_change_to: {eq: 'on'}})}, // i18n
        {caption: () => 'When the device is turned off', def: () => ({on_change_to: {eq: 'off'}})}, // i18n
        {caption: () => 'When the device is turned on or off', def: () => ({on_change: {}})}, // i18n
    ],
    [ChannelFunction.CONTAINER]: [
        {
            caption: () => 'When the fill level reaches a certain value', // i18n
            test: (t) => t.on_change_to && t.on_change_to.name === undefined,
            component: ReactionConditionThreshold,
            props: {
                min: () => 0, max: () => 100, step: () => 1,
                unit: () => '%',
                availableValues: (channel) => {
                    if (channel.config.fillLevelReportingInFullRange) {
                        return undefined;
                    } else {
                        return uniq([0, ...(channel.config.levelSensors || []).map(def => +def.fillLevel)]).sort((a, b) => a - b)
                    }
                },
                labelI18n: () => 'When the fill level will be', // i18n
                resumeLabelI18n: () => 'and wait until the fill level will be', // i18n
            },
        },
        {
            caption: () => 'When the fill level changes', // i18n
            def: () => ({on_change: {}})
        },
        {caption: () => 'When the fill level reading error occurs', def: () => ({on_change_to: {eq: 'on', name: 'invalid_value'}})}, // i18n
        {caption: () => 'When the fill level reading error disappears', def: () => ({on_change_to: {eq: 'off', name: 'invalid_value'}})}, // i18n
        {caption: () => 'When the alarm starts', def: () => ({on_change_to: {eq: 'on', name: 'alarm'}})}, // i18n
        {caption: () => 'When the alarm stops', def: () => ({on_change_to: {eq: 'off', name: 'alarm'}})}, // i18n
        {caption: () => 'When the warning starts', def: () => ({on_change_to: {eq: 'on', name: 'warning'}})}, // i18n
        {caption: () => 'When the warning stops', def: () => ({on_change_to: {eq: 'off', name: 'warning'}})}, // i18n
        {
            caption: () => 'When any of the sensors starts to report an invalid state', // i18n
            def: () => ({on_change_to: {eq: 'on', name: 'invalid_sensor_state'}}),
            canBeSetForChannel: (channel) => channel.config.levelSensors?.length > 0,
        },
        {
            caption: () => 'When any of the sensors stops to report an invalid state', // i18n
            def: () => ({on_change_to: {eq: 'off', name: 'invalid_sensor_state'}}),
            canBeSetForChannel: (channel) => channel.config.levelSensors?.length > 0,
        },
        {caption: () => 'When the sound alarm starts', def: () => ({on_change_to: {eq: 'on', name: 'sound_alarm_on'}})}, // i18n
        {caption: () => 'When the sound alarm stops', def: () => ({on_change_to: {eq: 'off', name: 'sound_alarm_on'}})}, // i18n
    ]
};

ChannelFunctionTriggers[ChannelFunction.THERMOMETER] = [
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][0],
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][1],
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][4],
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][5],
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][6],
];
ChannelFunctionTriggers[ChannelFunction.HUMIDITY] = [
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][2],
    ChannelFunctionTriggers[ChannelFunction.HUMIDITYANDTEMPERATURE][3],
];

ChannelFunctionTriggers[ChannelFunction.LIGHTSWITCH] = ChannelFunctionTriggers[ChannelFunction.POWERSWITCH];
ChannelFunctionTriggers[ChannelFunction.STAIRCASETIMER] = ChannelFunctionTriggers[ChannelFunction.POWERSWITCH];

ChannelFunctionTriggers[ChannelFunction.HVAC_THERMOSTAT_DIFFERENTIAL] = ChannelFunctionTriggers[ChannelFunction.HVAC_DOMESTIC_HOT_WATER];
ChannelFunctionTriggers[ChannelFunction.THERMOSTATHEATPOLHOMEPLUS] = ChannelFunctionTriggers[ChannelFunction.HVAC_DOMESTIC_HOT_WATER];
ChannelFunctionTriggers[ChannelFunction.HVAC_THERMOSTAT_HEAT_COOL] = ChannelFunctionTriggers[ChannelFunction.HVAC_THERMOSTAT];

ChannelFunctionTriggers[ChannelFunction.SEPTIC_TANK] = ChannelFunctionTriggers[ChannelFunction.CONTAINER];
ChannelFunctionTriggers[ChannelFunction.WATER_TANK] = ChannelFunctionTriggers[ChannelFunction.CONTAINER];

export function findTriggerDefinition(channelFunction, trigger) {
    return (ChannelFunctionTriggers[channelFunction] || []).find(t => t.test ? t.test(trigger) : isEqual(t.def(), trigger));
}

export function reactionTriggerCaption(reaction) {
    const triggerDef = findTriggerDefinition(reaction.owningChannel.functionId, reaction.trigger);
    if (triggerDef) {
        if ([ReactionConditionThreshold, ReactionConditionElectricitymeter].includes(triggerDef.component)) {
            const onChangeTo = reaction.trigger?.on_change_to || {};
            let operator;
            for (const op of ['eq', 'ne', 'lt', 'gt', 'le', 'ge']) {
                if (Object.hasOwn(onChangeTo, op)) {
                    operator = op;
                }
            }
            const operatorLabel = {eq: '=', ne: '≠', le: '≤', lt: '<', ge: '≥', gt: '>'}[operator];
            const unit = triggerDef.props.unit ? i18n.global.t(triggerDef.props.unit(onChangeTo.name, reaction.owningChannel)) : '';
            const unitBefore = triggerDef.props.unitBefore
                ? i18n.global.t(triggerDef.props.unitBefore(onChangeTo.name, reaction.owningChannel))
                : ''
            return i18n.global.t(triggerDef.props.labelI18n(onChangeTo.name)) + ` ${operatorLabel} ${unitBefore}${onChangeTo[operator]}${unit}`;
        } else {
            return i18n.global.t(triggerDef.caption(reaction.owningChannel));
        }
    } else {
        return i18n.global.t('When the condition is met');
    }
}


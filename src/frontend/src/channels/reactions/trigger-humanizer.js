import ChannelFunction from "@/common/enums/channel-function";
import {isEqual} from "lodash";

export const FIELD_NAMES = {
    color: '',
    color_brightness: '',
    brightness: '',
    temperature: 'the temperature', // i18n
    humidity: 'the humidity', // i18n
    flooding: '', // i18n
    manually_closed: '', // i18n
    voltage_avg: 'average voltage of all phases', // i18n
    voltage1: 'the voltage of phase 1', // i18n
    voltage2: 'the voltage of phase 2', // i18n
    voltage3: ' the voltage of phase 3', // i18n
    current_sum: 'summarized current of all phases', // i18n
    current1: 'the current of phase 1', // i18n
    current2: 'the current of phase 2', // i18n
    current3: 'the current of phase 3', // i18n
    power_active_sum: 'summarized active power of all phases', // i18n
    power_active1: 'the active power of phase 1', // i18n
    power_active2: 'the active power of phase 2', // i18n
    power_active3: 'the active power of phase 3', // i18n
    power_reactive_sum: 'summarized reactive power of all phases', // i18n
    power_reactive1: 'the reactive power of phase 1', // i18n
    power_reactive2: 'the reactive power of phase 2', // i18n
    power_reactive3: 'the reactive power of phase 3', // i18n
    power_apparent_sum: 'summarized apparent power of all phases', // i18n
    power_apparent1: 'the apparent power of phase 1', // i18n
    power_apparent2: 'the apparent power of phase 2', // i18n
    power_apparent3: 'the apparent power of phase 3', // i18n
};

export const DEFAULT_FIELD_NAMES = {
    [ChannelFunction.THERMOMETER]: 'the temperature', // di18n
    [ChannelFunction.HUMIDITY]: 'the humidity', // di18n
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: 'the garage door', // i18n
    [ChannelFunction.CONTROLLINGTHEGARAGEDOOR]: 'the garage door', // i18n
    [ChannelFunction.OPENINGSENSOR_GATE]: 'the gate', // i18n
    [ChannelFunction.CONTROLLINGTHEGATE]: 'the gate', // i18n
    [ChannelFunction.CONTROLLINGTHEGATEWAYLOCK]: 'the gateway', // i18n
    [ChannelFunction.OPENINGSENSOR_GATEWAY]: 'the gateway', // i18n
    [ChannelFunction.CONTROLLINGTHEDOORLOCK]: 'the door', // i18n
    [ChannelFunction.OPENINGSENSOR_DOOR]: 'the door', // i18n
    [ChannelFunction.LIGHTSWITCH]: 'the device', // i18n
    [ChannelFunction.POWERSWITCH]: 'the device', // i18n
    [ChannelFunction.CONTROLLINGTHEROLLERSHUTTER]: 'the roller shutter closed percentage', // i18n
    [ChannelFunction.CONTROLLINGTHEROOFWINDOW]: 'the roof window closed percentage', // i18n
    [ChannelFunction.OPENINGSENSOR_ROLLERSHUTTER]: 'the roller shutter', // i18n
    [ChannelFunction.OPENINGSENSOR_ROOFWINDOW]: 'the roof window', // i18n
};

export const STATIC_TRIGGERS = {
    [ChannelFunction.OPENINGSENSOR_GATE]: [
        {label: 'When the gate will be opened', trigger: {on_change_to: {eq: 'open'}}}, // i18n
        {label: 'When the gate will be closed', trigger: {on_change_to: {eq: 'closed'}}}, // i18n
        {label: 'When the gate will be opened or closed', trigger: {on_change: {}}}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {label: 'When the garage door will be opened', trigger: {on_change_to: {eq: 'open'}}}, // i18n
        {label: 'When the garage door will be closed', trigger: {on_change_to: {eq: 'closed'}}}, // i18n
        {label: 'When the garage door will be opened or closed', trigger: {on_change: {}}}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_GATEWAY]: [
        {label: 'When the gateway will be opened', trigger: {on_change_to: {eq: 'open'}}}, // i18n
        {label: 'When the gateway will be closed', trigger: {on_change_to: {eq: 'closed'}}}, // i18n
        {label: 'When the gateway will be opened or closed', trigger: {on_change: {}}}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_DOOR]: [
        {label: 'When the door will be opened', trigger: {on_change_to: {eq: 'open'}}}, // i18n
        {label: 'When the door will be closed', trigger: {on_change_to: {eq: 'closed'}}}, // i18n
        {label: 'When the door will be opened or closed', trigger: {on_change: {}}}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_ROLLERSHUTTER]: [
        {label: 'When the roller shutter will be opened', trigger: {on_change_to: {eq: 'open'}}}, // i18n
        {label: 'When the roller shutter will be closed', trigger: {on_change_to: {eq: 'closed'}}}, // i18n
        {label: 'When the roller shutter will be opened or closed', trigger: {on_change: {}}}, // i18n
    ],
    [ChannelFunction.OPENINGSENSOR_ROOFWINDOW]: [
        {label: 'When the roof window will be opened', trigger: {on_change_to: {eq: 'open'}}}, // i18n
        {label: 'When the roof window will be closed', trigger: {on_change_to: {eq: 'closed'}}}, // i18n
        {label: 'When the roof window will be opened or closed', trigger: {on_change: {}}}, // i18n
    ],
    [ChannelFunction.CONTROLLINGTHEROLLERSHUTTER]: [
        {label: 'When the roller shutter will change', trigger: {on_change: {}}}, // i18n
    ],
};

const STATES = {
    hi: 'in high state', // i18n
    closed: 'closed', // i18n
    on: 'turned on', // i18n
    lo: 'in low state', // i18n
    low: 'in low state', // i18n
    open: 'opened', // i18n
    off: 'turned off', // i18n
};

function translateState(state, vue) {
    if (STATES[state]) {
        return vue.$t(STATES[state]);
    } else {
        return state;
    }
}

export function triggerHumanizer(channelFunction, trigger, vue) {
    if (STATIC_TRIGGERS[channelFunction]) {
        const customTrigger = STATIC_TRIGGERS[channelFunction].find(ct => isEqual(ct.trigger, trigger));
        if (customTrigger) {
            return vue.$t(customTrigger.label);
        }
    }
    const onChangeTo = trigger?.on_change_to || {};
    let field = vue.$t('the sensor');
    if (onChangeTo.name && FIELD_NAMES[onChangeTo.name]) {
        field = vue.$t(FIELD_NAMES[onChangeTo.name]);
    } else if (!trigger.name && DEFAULT_FIELD_NAMES[channelFunction]) {
        field = vue.$t(DEFAULT_FIELD_NAMES[channelFunction]);
    } else if (onChangeTo.name) {
        field = vue.$t(onChangeTo.name);
    }
    let operator;
    for (const op of ['eq', 'ne', 'lt', 'gt', 'le', 'ge']) {
        if (Object.hasOwn(onChangeTo, op)) {
            operator = op;
        }
    }
    if (operator) {
        const value = translateState(onChangeTo[operator], vue);
        if (Number.isFinite(value)) {
            const operatorLabel = {eq: '=', ne: '≠', le: '≤', lt: '<', ge: '≥', gt: '>'}[operator];
            return vue.$t('When {field} {operator} {value}', {field, operator: operatorLabel || operator, value});
        } else {
            return vue.$t('When {field} will be {value}', {field, value});
        }
    }
    return vue.$t('When the condition is met');
}

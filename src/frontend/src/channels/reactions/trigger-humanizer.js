import ChannelFunction from "@/common/enums/channel-function";

const FIELD_NAMES = {
    temperature: 'the temperature', // i18n
    humidity: 'the humidity', // i18n
    voltage1: 'the voltage of phase 1', // i18n
};

const DEFAULT_FIELD_NAMES = {
    [ChannelFunction.THERMOMETER]: 'the temperature', // di18n
    [ChannelFunction.HUMIDITY]: 'the humidity', // di18n
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: 'the garage door', // i18n
    [ChannelFunction.LIGHTSWITCH]: 'the device', // i18n
    [ChannelFunction.POWERSWITCH]: 'the device', // i18n
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

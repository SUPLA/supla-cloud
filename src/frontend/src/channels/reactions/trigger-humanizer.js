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
    if (Object.hasOwn(onChangeTo, 'eq')) {
        const value = translateState(onChangeTo.eq, vue);
        if (Number.isFinite(value)) {
            return vue.$t('When {field} will be equal to {value}', {field, value});
        } else {
            return vue.$t('When {field} will be {value}', {field, value});
        }
    } else if (Object.hasOwn(onChangeTo, 'ne')) {
        const value = translateState(onChangeTo.ne, vue);
        if (Number.isFinite(value)) {
            return vue.$t('When {field} will be different than {value}', {field, value});
        } else {
            return vue.$t('When {field} will not be {value}', {field, value});
        }
    } else if (Object.hasOwn(onChangeTo, 'lt')) {
        return vue.$t('When {field} will be lower than {value}', {field, value: onChangeTo.lt});
    } else if (Object.hasOwn(onChangeTo, 'gt')) {
        return vue.$t('When {field} will be greater than {value}', {field, value: onChangeTo.gt});
    } else if (Object.hasOwn(onChangeTo, 'le')) {
        return vue.$t('When {field} will be lower than or equal to {value}', {field, value: onChangeTo.le});
    } else if (Object.hasOwn(onChangeTo, 'ge')) {
        return vue.$t('When {field} will be greater than or equal to {value}', {field, value: onChangeTo.ge});
    }
    return vue.$t('When the condition is met');
}

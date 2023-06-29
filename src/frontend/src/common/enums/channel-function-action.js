const ChannelFunctionAction = Object.freeze({
    READ: 1000,
    SET: 2000,
    EXECUTE: 3000,
    INTERRUPT: 3001,
    INTERRUPT_AND_EXECUTE: 3002,
    OPEN: 10,
    CLOSE: 20,
    SHUT: 30,
    REVEAL: 40,
    REVEAL_PARTIALLY: 50,
    SHUT_PARTIALLY: 51,
    TURN_ON: 60,
    TURN_OFF: 70,
    SET_RGBW_PARAMETERS: 80,
    OPEN_CLOSE: 90,
    STOP: 100,
    TOGGLE: 110,
    OPEN_PARTIALLY: 120,
    CLOSE_PARTIALLY: 130,
    UP_OR_STOP: 140,
    DOWN_OR_STOP: 150,
    STEP_BY_STEP: 160,
    ENABLE: 200,
    DISABLE: 210,
    SEND: 220,
    COPY: 10100,
    AT_FORWARD_OUTSIDE: 10000,
    AT_DISABLE_LOCAL_FUNCTION: 10200,

    requiresParams(actionId) {
        return [
            ChannelFunctionAction.SET,
            ChannelFunctionAction.REVEAL_PARTIALLY,
            ChannelFunctionAction.SHUT_PARTIALLY,
            ChannelFunctionAction.SET_RGBW_PARAMETERS,
            ChannelFunctionAction.OPEN_PARTIALLY,
            ChannelFunctionAction.CLOSE_PARTIALLY,
            ChannelFunctionAction.COPY,
            ChannelFunctionAction.SEND,
        ].includes(actionId);
    },

    paramsValid(actionId, params) {
        switch (actionId) {
            case ChannelFunctionAction.SET:
                return params.transparent?.length > 0 || params.opaque?.length > 0;
            case ChannelFunctionAction.SET_RGBW_PARAMETERS:
                return !!(params.brightness >= 0 || params.brightness <= 100 || params.hue
                    || params.colorBrightness >= 0 || params.colorBrightness <= 100);
            case ChannelFunctionAction.REVEAL_PARTIALLY:
            case ChannelFunctionAction.SHUT_PARTIALLY:
            case ChannelFunctionAction.OPEN_PARTIALLY:
            case ChannelFunctionAction.CLOSE_PARTIALLY:
                return params.percentage >= 0 && params.percentage <= 100;
            case ChannelFunctionAction.COPY:
                return !!params.sourceChannelId;
            case ChannelFunctionAction.SEND:
                return !!((params.title || params.body) && params.accessIds?.length > 0);
            default:
                return true;
        }
    },

    availableInSchedules(actionId) {
        return ![
            ChannelFunctionAction.OPEN_CLOSE,
            ChannelFunctionAction.TOGGLE,
            ChannelFunctionAction.STOP,
            ChannelFunctionAction.UP_OR_STOP,
            ChannelFunctionAction.DOWN_OR_STOP,
            ChannelFunctionAction.STEP_BY_STEP,
            ChannelFunctionAction.SEND,
        ].includes(actionId);
    }
});

export default ChannelFunctionAction;

const ChannelFunctionAction = Object.freeze({
    READ: 1000,
    SET: 2000,
    EXECUTE: 3000,
    OPEN: 10,
    CLOSE: 20,
    SHUT: 30,
    REVEAL: 40,
    REVEAL_PARTIALLY: 50,
    TURN_ON: 60,
    TURN_OFF: 70,
    SET_RGBW_PARAMETERS: 80,
    OPEN_CLOSE: 90,
    STOP: 100,
    TOGGLE: 110,
    OPEN_PARTIALLY: 120,
    COPY: 10100,
    AT_FORWARD_OUTSIDE: 10000,
    AT_DISABLE_LOCAL_FUNCTION: 10200,
});

export default ChannelFunctionAction;

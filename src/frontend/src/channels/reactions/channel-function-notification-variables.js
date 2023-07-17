import ChannelFunction from "@/common/enums/channel-function";

export const ChannelFunctionNotificationVariables = {
    [ChannelFunction.THERMOMETER]: [
        {label: 'Temperature', code: '{temperature}'}, // i18n
    ],
    [ChannelFunction.HUMIDITY]: [
        {label: 'Humidity', code: '{humidity}'}, // i18n
    ],
    [ChannelFunction.HUMIDITYANDTEMPERATURE]: [
        {label: 'Temperature', code: '{temperature}'}, // i18n
        {label: 'Humidity', code: '{humidity}'}, // i18n
    ],
};

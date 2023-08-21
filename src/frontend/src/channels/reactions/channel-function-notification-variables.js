import ChannelFunction from "@/common/enums/channel-function";

export const ChannelFunctionNotificationVariables = (channel) => {
    switch (channel.functionId) {
        case ChannelFunction.THERMOMETER:
            return [
                {label: 'Temperature', value: '{temperature}'}, // i18n
            ];
        case ChannelFunction.HUMIDITY:
            return [
                {label: 'Humidity', value: '{humidity}'}, // i18n
            ];
        case ChannelFunction.HUMIDITYANDTEMPERATURE:
            return [
                {label: 'Temperature', value: '{temperature}'}, // i18n
                {label: 'Humidity', value: '{humidity}'}, // i18n
            ];
        case ChannelFunction.ELECTRICITYMETER: {
            const enabledPhases = channel.config.enabledPhases || [1, 2, 3];
            const variables = [];
            variables.push({label: 'Average voltage', value: '{voltage_avg}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({label: `Voltage of phase ${phaseNo}`, value: `{voltage${phaseNo}}`}));
            variables.push({label: 'Current', value: '{current_sum}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({label: `Current of phase ${phaseNo}`, value: `{current${phaseNo}}`}));
            variables.push({label: 'Power active', value: '{power_active_sum}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({
                label: `Power active of phase ${phaseNo}`,
                value: `{power_active${phaseNo}}`
            }));
            variables.push({label: 'Power reactive', value: '{power_reactive_sum}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({
                label: `Power reactive of phase ${phaseNo}`,
                value: `{power_reactive${phaseNo}}`
            }));
            variables.push({label: 'Power apparent', value: '{power_apparent_sum}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({
                label: `Power apparent of phase ${phaseNo}`,
                value: `{power_apparent${phaseNo}}`
            }));
            variables.push({label: 'Forward active energy', value: '{fae_sum}'}); // i18n
            variables.push({label: 'Forward active energy balanced', value: '{fae_balanced}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({
                label: `Forward active energy of phase ${phaseNo}`,
                value: `{fae${phaseNo}}`
            }));
            variables.push({label: 'Reverse active energy', value: '{rae_sum}'}); // i18n
            variables.push({label: 'Reverse active energy balanced', value: '{rae_balanced}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({
                label: `Reverse active energy of phase ${phaseNo}`,
                value: `{rae${phaseNo}}`
            }));
            return variables;
        }
        default:
            return [];
    }
};

import ChannelFunction from "@/common/enums/channel-function";

export const ChannelFunctionNotificationVariables = (channel) => {
    switch (channel.functionId) {
        case ChannelFunction.THERMOMETER:
            return [
                {label: 'Temperature', code: '{temperature}'}, // i18n
            ];
        case ChannelFunction.HUMIDITY:
            return [
                {label: 'Humidity', code: '{humidity}'}, // i18n
            ];
        case ChannelFunction.HUMIDITYANDTEMPERATURE:
            return [
                {label: 'Temperature', code: '{temperature}'}, // i18n
                {label: 'Humidity', code: '{humidity}'}, // i18n
            ];
        case ChannelFunction.ELECTRICITYMETER: {
            const enabledPhases = channel.config.enabledPhases || [1, 2, 3];
            const variables = [];
            variables.push({label: 'Average voltage', code: '{voltage_avg}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({label: `Voltage of phase ${phaseNo}`, code: `{voltage${phaseNo}}`}));
            variables.push({label: 'Total current', code: '{current_sum}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({label: `Current of phase ${phaseNo}`, code: `{current${phaseNo}}`}));
            variables.push({label: 'Total power active', code: '{power_active_sum}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({label: `Power active of phase ${phaseNo}`, code: `{power_active${phaseNo}}`}));
            variables.push({label: 'Total power reactive', code: '{power_reactive_sum}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({
                label: `Power reactive of phase ${phaseNo}`,
                code: `{power_reactive${phaseNo}}`
            }));
            variables.push({label: 'Total power apparent', code: '{power_apparent_sum}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({
                label: `Power apparent of phase ${phaseNo}`,
                code: `{power_apparent${phaseNo}}`
            }));
            variables.push({label: 'Total forward active energy', code: '{fae_sum}'}); // i18n
            variables.push({label: 'Forward active energy balanced', code: '{fae_balanced}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({label: `Forward active energy of phase ${phaseNo}`, code: `{fae${phaseNo}}`}));
            variables.push({label: 'Total reverse active energy', code: '{rae_sum}'}); // i18n
            variables.push({label: 'Reverse active energy balanced', code: '{rae_balanced}'}); // i18n
            enabledPhases.forEach(phaseNo => variables.push({label: `Reverse active energy of phase ${phaseNo}`, code: `{rae${phaseNo}}`}));
            return variables;
        }
        default:
            return [];
    }
};

import {i18n} from "@/locale";
import ChannelFunction from "@/common/enums/channel-function";
import ChannelFunctionAction from "@/common/enums/channel-function-action";

export function measurementUnit(channel) {
    if (channel.config && channel.config.unit) {
        return channel.config.unit;
    }
    switch (channel.function.name) {
        case 'IC_HEATMETER':
            return 'GJ';
        case 'ELECTRICITYMETER':
        case 'IC_ELECTRICITYMETER':
            return 'kWh';
        default:
            return 'm³';
    }
}

export function actionCaption(action, subject) {
    const functionId = subject?.functionId || subject?.id || subject || 0;
    const customLabels = {
        [ChannelFunction.CONTROLLINGTHEROOFWINDOW]: {
            [ChannelFunctionAction.REVEAL]: 'Open', // i18n
            [ChannelFunctionAction.SHUT]: 'Close', // i18n
            [ChannelFunctionAction.REVEAL_PARTIALLY]: 'Open partially', // i18n
            [ChannelFunctionAction.SHUT_PARTIALLY]: 'Close partially', // i18n
        },
    }
    const caption = customLabels[functionId]?.[action.id] || action.caption;
    return i18n.global.t(caption);
}

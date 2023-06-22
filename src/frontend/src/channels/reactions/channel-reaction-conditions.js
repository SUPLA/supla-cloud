import ChannelFunction from "@/common/enums/channel-function";

export const ChannelReactionConditions = {
    [ChannelFunction.OPENINGSENSOR_GARAGEDOOR]: [
        {caption: 'gate was opened', id: 'open', def: () => ({on_change_to: {eq: 'open'}})}, // i18n
        {caption: 'gate was closed', id: 'close', def: () => ({on_change_to: {eq: 'closed'}})}, // i18n
    ],
};

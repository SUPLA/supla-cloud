export function useDisplaySettings(channel) {
    function canDisplaySetting(settingName) {
        return !channel?.config.hiddenConfigFields?.includes(settingName);
    }

    function canDisplayAnySetting(...settingNames) {
        return !!settingNames.find((s) => canDisplaySetting(s));
    }

    return {canDisplaySetting, canDisplayAnySetting}
}

import Vue from "vue";
import "./filters-date";
import {i18n} from "@/locale";
import {useCurrentUserStore} from "@/stores/current-user-store";

export function withBaseUrl(url, absolute = true) {
    if (url[0] != '/') {
        url = '/' + url;
    }
    const {serverUrl} = useCurrentUserStore();
    return (serverUrl ? ((absolute && serverUrl) || '') : '') + url;
}

export function withDownloadAccessToken(url) {
    const {filesToken} = useCurrentUserStore();
    return withBaseUrl(url) + 'access_token=' + filesToken;
}

export function channelTitle(channel) {
    return channel.caption || `ID${channel.id} ` + i18n.global.t(channel.function ? channel.function.caption : 'None');
}

export function channelIconUrl(channel) {
    if (channel.userIconId) {
        return withDownloadAccessToken(`/api/user-icons/${channel.userIconId}/0?`);
    } else {
        const alternative = channel.altIcon ? '_' + channel.altIcon : '';
        return withBaseUrl(`assets/img/functions/${channel.function.id}${alternative}.svg`);
    }
}

export function deviceTitle(device) {
    return `${device.location.caption} / ${device.comment || device.name}`;
}

export function ellipsis(string, length = 20) {
    return string.length > length ? string.substr(0, length - 3) + '...' : string;
}

export function prettyBytes(bytes) {
    if (typeof bytes !== 'number' || isNaN(bytes)) {
        throw new TypeError('Expected a number');
    }
    const units = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    if (bytes < 1024) {
        return bytes + ' B';
    }
    const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    bytes = (bytes / Math.pow(1024, exponent)).toFixed(2) * 1;
    const unit = units[exponent];
    return `${bytes} ${unit}`;
}

export function roundToDecimals(num, decimals = 2) {
    const multiplier = Math.pow(10, decimals);
    return Math.round(num * multiplier) / multiplier;
}

export function prettyMilliseconds(ms) {
    if (typeof ms !== 'number' || isNaN(ms)) {
        throw new TypeError('Expected a number');
    }
    if (ms < 1000) {
        return ms + ' ms';
    } else if (ms < 60000) {
        return (Math.round(ms / 100) / 10) + ' ' + i18n.global.t('sec.');
    } else if (ms < 3600000) {
        let formatted = Math.floor(ms / 60000) + ' ' + i18n.global.t('min.');
        if (ms % 60000) {
            formatted += ' ' + prettyMilliseconds(ms % 60000);
        }
        return formatted;
    } else if (ms < 86400000) {
        const value = Math.floor(ms / 3600000);
        let formatted = value + ' ' + (value === 1 ? i18n.global.t('hour') : i18n.global.t('hours'));
        if (ms % 3600000) {
            formatted += ' ' + prettyMilliseconds(ms % 3600000);
        }
        return formatted;
    } else {
        const value = Math.floor(ms / 86400000);
        let formatted = value + ' ' + (value === 1 ? i18n.global.t('day') : i18n.global.t('days'));
        if (ms % 86400000) {
            formatted += ' ' + prettyMilliseconds(ms % 86400000);
        }
        return formatted;
    }
}

export function formatGpmValue(value, config) {
    if (value === null) {
        return '---';
    }
    const roundedValue = roundToDecimals(value, config?.valuePrecision || 0);
    const unitBefore = ((config?.unitBeforeValue || '') + (config?.noSpaceBeforeValue ? '' : ' '));
    const unitAfter = ((config?.noSpaceAfterValue ? '' : ' ') + (config?.unitAfterValue || ''));
    return (unitBefore + roundedValue + unitAfter).trim();
}

Vue.filter('withBaseUrl', withBaseUrl);
Vue.filter('withDownloadAccessToken', withDownloadAccessToken);
Vue.filter('deviceTitle', deviceTitle);
Vue.filter('toUpperCase', (text) => text.toUpperCase());
Vue.filter('ellipsis', ellipsis);
Vue.filter('prettyBytes', prettyBytes);
Vue.filter('roundToDecimals', roundToDecimals);
Vue.filter('formatGpmValue', formatGpmValue);

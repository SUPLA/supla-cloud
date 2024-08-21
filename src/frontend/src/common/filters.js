import Vue from "vue";
import "./filters-date";

export function withBaseUrl(url, absolute = true) {
    if (url[0] != '/') {
        url = '/' + url;
    }
    return (Vue.prototype.$user ? ((absolute && Vue.prototype.$user.serverUrl) || '') : '') + (Vue.config.external?.baseUrl || '') + url;
}

export function withDownloadAccessToken(url) {
    return withBaseUrl(url) + 'access_token=' + Vue.prototype.$user.getFilesDownloadToken();
}

export function channelTitle(channel, vue, withDevice = false) {
    return (channel.caption || `ID${channel.id} ` + vue.$t(channel.function ? channel.function.caption : 'None'))
        + (withDevice && channel.iodevice ? ' ('
            + deviceTitle({location: channel.location, comment: channel.iodevice.comment, name: channel.iodevice.name}) + ')' : '');
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

export function prettyMilliseconds(ms, vue) {
    if (typeof ms !== 'number' || isNaN(ms)) {
        throw new TypeError('Expected a number');
    }
    if (ms < 1000) {
        return ms + ' ms';
    } else if (ms < 60000) {
        return (Math.round(ms / 100) / 10) + ' ' + vue.$t('sec.');
    } else if (ms < 3600000) {
        let formatted = Math.floor(ms / 60000) + ' ' + vue.$t('min.');
        if (ms % 60000) {
            formatted += ' ' + prettyMilliseconds(ms % 60000, vue);
        }
        return formatted;
    } else if (ms < 86400000) {
        const value = Math.floor(ms / 3600000);
        let formatted = value + ' ' + (value === 1 ? vue.$t('hour') : vue.$t('hours'));
        if (ms % 3600000) {
            formatted += ' ' + prettyMilliseconds(ms % 3600000, vue);
        }
        return formatted;
    } else {
        const value = Math.floor(ms / 86400000);
        let formatted = value + ' ' + (value === 1 ? vue.$t('day') : vue.$t('days'));
        if (ms % 86400000) {
            formatted += ' ' + prettyMilliseconds(ms % 86400000, vue);
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

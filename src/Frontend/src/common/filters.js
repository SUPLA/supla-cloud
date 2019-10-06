import Vue from "vue";

export function withBaseUrl(url, absolute = true) {
    if (url[0] != '/') {
        url = '/' + url;
    }
    return (Vue.prototype.$user ? ((absolute && Vue.prototype.$user.serverUrl) || '') : '') + Vue.config.external.baseUrl + url;
}

export function withDownloadAccessToken(url) {
    return withBaseUrl(url) + 'access_token=' + Vue.prototype.$user.getFilesDownloadToken();
}

// https://stackoverflow.com/a/8105740/878514
export function intToIp(int) {
    const part1 = int & 255;
    const part2 = ((int >> 8) & 255);
    const part3 = ((int >> 16) & 255);
    const part4 = ((int >> 24) & 255);
    return part4 + "." + part3 + "." + part2 + "." + part1;
}

export function channelTitle(channel, vue, withDevice = false) {
    return `ID${channel.id} ` + (channel.caption || vue.$t(channel.function ? channel.function.caption : 'None'))
        + (withDevice && channel.iodevice ? ' (' + deviceTitle(channel.iodevice, channel.location) + ')' : '');
}

export function channelIconUrl(channel) {
    if (channel.userIconId) {
        return withDownloadAccessToken(`/api/user-icons/${channel.userIconId}/0?`);
    } else {
        const alternative = channel.altIcon ? '_' + channel.altIcon : '';
        return withBaseUrl(`assets/img/functions/${channel.function.id}${alternative}.svg`);
    }
}

export function deviceTitle(device, channelLocation = undefined) {
    return `${(channelLocation || device.location || {}).caption} / ${device.comment || device.name}`;
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

Vue.filter('withBaseUrl', withBaseUrl);
Vue.filter('withDownloadAccessToken', withDownloadAccessToken);
Vue.filter('intToIp', intToIp);
Vue.filter('channelTitle', channelTitle);
Vue.filter('deviceTitle', deviceTitle);
Vue.filter('toUpperCase', (text) => text.toUpperCase());
Vue.filter('ellipsis', ellipsis);
Vue.filter('prettyBytes', prettyBytes);
Vue.filter('roundToDecimals', roundToDecimals);

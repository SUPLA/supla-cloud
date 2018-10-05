import Vue from "vue";

export function withBaseUrl(url) {
    if (url[0] != '/') {
        url = '/' + url;
    }
    return Vue.config.external.baseUrl + url;
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
        + (withDevice && channel.iodevice ? ' (' + deviceTitle(channel.iodevice) + ')' : '');
}

export function deviceTitle(device) {
    return `${device.location.caption} / ${device.comment || device.name}`;
}

export function ellipsis(string, length = 20) {
    return string.length > length ? string.substr(0, length - 3) + '...' : string;
}

Vue.filter('withBaseUrl', withBaseUrl);
Vue.filter('withDownloadAccessToken', withDownloadAccessToken);
Vue.filter('intToIp', intToIp);
Vue.filter('channelTitle', channelTitle);
Vue.filter('deviceTitle', deviceTitle);
Vue.filter('toUpperCase', (text) => text.toUpperCase());
Vue.filter('ellipsis', ellipsis);

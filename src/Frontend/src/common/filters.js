import Vue from "vue";

export function withBaseUrl(url) {
    if (url[0] != '/') {
        url = '/' + url;
    }
    return Vue.config.external.baseUrl + url;
}

// https://stackoverflow.com/a/8105740/878514
function intToIp(int) {
    const part1 = int & 255;
    const part2 = ((int >> 8) & 255);
    const part3 = ((int >> 16) & 255);
    const part4 = ((int >> 24) & 255);
    return part4 + "." + part3 + "." + part2 + "." + part1;
}

export function channelTitle(channel, vue, withDevice = false) {
    return (channel.caption || vue.$t(channel.function.caption)) + (withDevice ? ' (' + deviceTitle(channel.iodevice) + ')' : '');
}

export function deviceTitle(device) {
    return `${device.location.caption} / ${device.comment || device.name}`;
}

Vue.filter('withBaseUrl', withBaseUrl);
Vue.filter('intToIp', intToIp);
Vue.filter('channelTitle', channelTitle);
Vue.filter('deviceTitle', deviceTitle);

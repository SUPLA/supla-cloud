import Vue from "vue";

export function withBaseUrl(url) {
    if (url[0] != '/') {
        url = '/' + url;
    }
    return (Vue.config.external.baseUrl || '') + url;
}
Vue.filter('withBaseUrl', withBaseUrl);

// https://stackoverflow.com/a/8105740/878514
function intToIp(int) {
    const part1 = int & 255;
    const part2 = ((int >> 8) & 255);
    const part3 = ((int >> 16) & 255);
    const part4 = ((int >> 24) & 255);
    return part4 + "." + part3 + "." + part2 + "." + part1;
}
Vue.filter('intToIp', intToIp);

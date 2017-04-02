import Vue from "vue";

export function withBaseUrl(url) {
    if (url[0] != '/') {
        url = '/' + url;
    }
    return `${Vue.http.options.root}` + url;
}

Vue.filter('withBaseUrl', withBaseUrl);

import Vue from "vue";

export function withBaseUrl(url) {
    return `${Vue.http.options.root}` + url;
}

Vue.filter('withBaseUrl', withBaseUrl);

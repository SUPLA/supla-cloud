import {createFetch} from "@vueuse/core";
import Vue from "vue";

export const useSuplaApi = createFetch({
    options: {
        async beforeFetch({url, options}) {
            const token = Vue.prototype.$user?.getToken();
            if (token) {
                options.headers.Authorization = `Bearer ${token}`
            }
            options.headers['X-Accept-Version'] = '3';
            options.headers['X-Client-Version'] = Vue.prototype.$frontendVersion;
            const serverUrl = Vue.prototype.$user?.determineServerUrl() || '';
            url = serverUrl + Vue.config.external.baseUrl + '/api/' + url
            return {url, options}
        },
    },
})

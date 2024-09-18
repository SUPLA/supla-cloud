import {createFetch} from "@vueuse/core";
import Vue from "vue";
import {useFrontendConfigStore} from "@/stores/frontend-config-store";

export const useSuplaApi = createFetch({
    options: {
        async beforeFetch({url, options}) {
            const {frontendVersion, baseUrl} = useFrontendConfigStore();
            const token = Vue.prototype.$user?.getToken();
            if (token) {
                options.headers.Authorization = `Bearer ${token}`
            }
            options.headers['X-Accept-Version'] = '3';
            options.headers['X-Client-Version'] = frontendVersion;
            const serverUrl = Vue.prototype.$user?.determineServerUrl() || '';
            url = serverUrl + baseUrl + '/api/' + url
            return {url, options}
        },
    },
})

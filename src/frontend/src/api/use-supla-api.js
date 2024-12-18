import {createFetch} from "@vueuse/core";
import Vue from "vue";
import {useFrontendConfigStore} from "@/stores/frontend-config-store";
import {errorNotification} from "@/common/notifier";
import {i18n} from "@/locale";

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
        async onFetchError(e) {
            if (e.error?.name === 'AbortError') {
                return;
            }
            const response = e.response;
            if (response?.status == 401) {
                window.location.assign(window.location.toString());
            } else {
                let body;
                try {
                    body = await response?.json();
                } catch (e) {
                    body = '';
                }
                let message = body?.message || 'Error when communicating the server. Try again in a while.'; // i18n
                const details = body?.details || {};
                if (details.propertyPath && message.indexOf('{propertyPath}') === -1) {
                    message += ' ' + i18n.global.t('Field: {propertyPath}.', details);
                }
                errorNotification(i18n.global.t('Error'), i18n.global.t(message, details));
            }
        }
    },
})

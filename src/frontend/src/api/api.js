import {useFrontendConfigStore} from "@/stores/frontend-config-store";
import Vue from "vue";
import {i18n} from "@/locale";
import {errorNotification} from "@/common/notifier";

function getDefaultHeaders() {
    const token = Vue.prototype.$user?.getToken();
    const frontendConfig = useFrontendConfigStore();
    return {
        Authorization: `Bearer ${token}`,
        'X-Accept-Version': '3',
        'X-Client-Version': frontendConfig.frontendVersion,
    }
}

function buildAbsoluteUrl(endpoint) {
    const frontendConfig = useFrontendConfigStore();
    const serverUrl = Vue.prototype.$user?.determineServerUrl() || '';
    return serverUrl + frontendConfig.baseUrl + '/api/' + endpoint;
}

function responseHandler(request, config) {
    return (response) => {
        if (response.status == 401) {
            window.location.assign(window.location.toString());
        } else {
            return response.text().then(text => {
                let body = text && JSON.parse(text);
                if (!body) {
                    body = text;
                }
                const status = response.status;
                if (!response.ok) {
                    const skip = config.skipErrorHandler &&
                        (!Array.isArray(config.skipErrorHandler) || config.skipErrorHandler.indexOf(status) >= 0);
                    if (!skip) {
                        let message = body?.message || 'Error when communicating the server. Try again in a while.'; // i18n
                        const details = body?.details || {};
                        if (details.propertyPath && message.indexOf('{propertyPath}') === -1) {
                            message += ' ' + i18n.global.t('Field: {propertyPath}.', details);
                        }
                        errorNotification(i18n.global.t('Error'), i18n.global.t(message, details));
                    }
                    return Promise.reject({body, status});
                }
                return {body, status};
            });
        }

    }
}

function get(endpoint, config = {}) {
    const requestOptions = {
        method: 'GET',
        headers: getDefaultHeaders(),
    };
    return fetch(buildAbsoluteUrl(endpoint), requestOptions).then(responseHandler(requestOptions, config));
}

function patch(endpoint, body, config = {}) {
    const requestOptions = {
        method: 'PATCH',
        headers: getDefaultHeaders(),
        body: JSON.stringify(body),
    };
    return fetch(buildAbsoluteUrl(endpoint), requestOptions).then(responseHandler(requestOptions, config));
}

function delete_(endpoint, config = {}) {
    const requestOptions = {
        method: 'DELETE',
        headers: getDefaultHeaders(),
    };
    return fetch(buildAbsoluteUrl(endpoint), requestOptions).then(responseHandler(requestOptions, config));
}

export const api = {
    get,
    patch,
    delete_,
};

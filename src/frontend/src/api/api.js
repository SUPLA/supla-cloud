import {useFrontendConfigStore} from '@/stores/frontend-config-store';
import {i18n} from '@/locale';
import {errorNotification} from '@/common/notifier';
import {useCurrentUserStore} from '@/stores/current-user-store';

function getDefaultHeaders() {
  const frontendConfig = useFrontendConfigStore();
  const headers = {
    Accept: 'application/json',
    'X-Accept-Version': '3',
    'X-Client-Version': frontendConfig.frontendVersion,
  };
  const currentUser = useCurrentUserStore();
  const token = currentUser.userToken;
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }
  return headers;
}

function getDefaultHeadersJson() {
  return {...getDefaultHeaders(), 'Content-Type': 'application/json'};
}

function buildAbsoluteUrl(endpoint) {
  const currentUser = useCurrentUserStore();
  const urlEndpoint = endpoint.startsWith('/') ? endpoint : '/api/' + endpoint;
  return (currentUser.serverUrl || '') + urlEndpoint;
}

function responseHandler(request, config) {
  return (response) => {
    const skip = config.skipErrorHandler && (!Array.isArray(config.skipErrorHandler) || config.skipErrorHandler.includes(response?.status));
    if (response.status === 401 && !skip) {
      useCurrentUserStore().forget();
      window.location.assign(window.location.toString());
    } else {
      return response.text().then((text) => {
        let body;
        try {
          body = JSON.parse(text);
        } catch (e) {
          body = text;
        }
        const status = response.status;
        if (!response.ok) {
          const skip = config.skipErrorHandler && (!Array.isArray(config.skipErrorHandler) || config.skipErrorHandler.indexOf(status) >= 0);
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
        return {body, status, headers: response.headers};
      });
    }
  };
}

function get(endpoint, config = {}) {
  const requestOptions = {
    method: 'GET',
    headers: getDefaultHeaders(),
  };
  return fetch(buildAbsoluteUrl(endpoint), requestOptions).then(responseHandler(requestOptions, config));
}

function post(endpoint, body, config = {}) {
  const requestOptions = {
    method: 'POST',
    headers: body instanceof FormData ? getDefaultHeaders() : getDefaultHeadersJson(),
    body: body instanceof FormData ? body : JSON.stringify(body),
  };
  return fetch(buildAbsoluteUrl(endpoint), requestOptions).then(responseHandler(requestOptions, config));
}

function put(endpoint, body, config = {}) {
  const requestOptions = {
    method: 'PUT',
    headers: getDefaultHeadersJson(),
    body: JSON.stringify(body),
  };
  return fetch(buildAbsoluteUrl(endpoint), requestOptions).then(responseHandler(requestOptions, config));
}

function patch(endpoint, body, config = {}) {
  const requestOptions = {
    method: 'PATCH',
    headers: getDefaultHeadersJson(),
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

function head(endpoint, config = {}) {
  const requestOptions = {
    method: 'HEAD',
    headers: getDefaultHeadersJson(),
  };
  return fetch(buildAbsoluteUrl(endpoint), requestOptions).then(responseHandler(requestOptions, config));
}

export const api = {
  get,
  post,
  put,
  patch,
  delete_,
  head,
};

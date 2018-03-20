import {errorNotification} from "../notifier";

export default function (vue) {
    return function (request, next) {
        next(function (response) {
            if (!response.ok && !request.skipErrorHandler) {
                const message = (response.body && response.body.message)
                    || 'Error when communicating the server. Try again in a while.';
                const details = (response.body && response.body.details) || {};
                errorNotification(vue.$t('Error'), vue.$t(message, details));
            }
        });
    };
}

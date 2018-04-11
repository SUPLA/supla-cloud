import {errorNotification} from "../notifier";

export default function (vue) {
    return function (request, next) {
        next(function (response) {
            if (!response.ok) {
                const skip = request.skipErrorHandler &&
                    (!Array.isArray(request.skipErrorHandler) || request.skipErrorHandler.indexOf(response.status) >= 0);
                if (!skip) {
                    if (response.status == 401) {
                        window.location.assign(window.location.toString());
                    } else {
                        const message = (response.body && response.body.message)
                            || 'Error when communicating the server. Try again in a while.';
                        const details = (response.body && response.body.details) || {};
                        errorNotification(vue.$t('Error'), vue.$t(message, details));
                    }
                }
            }
        });
    };
}

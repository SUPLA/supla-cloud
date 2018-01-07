import {errorNotification} from "../notifier";

export default function (vue) {
    return function (request, next) {
        next(function (response) {
            if (!response.ok && !request.skipErrorHandler) {
                const message = (response.body && response.body.message)
                    || vue.$t('Error when communicating the server. Try again in a while.');
                errorNotification(vue.$t('Error'), message);
            }
        });
    };
}

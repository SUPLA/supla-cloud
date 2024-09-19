import {errorNotification} from "../notifier";
import {i18n} from "@/locale";

export default function () {
    return function (request, next) {
        next(function (response) {
            if (!response.ok) {
                const skip = request.skipErrorHandler &&
                    (!Array.isArray(request.skipErrorHandler) || request.skipErrorHandler.indexOf(response.status) >= 0);
                if (!skip) {
                    if (response.status == 401) {
                        window.location.assign(window.location.toString());
                    } else {
                        let message = (response.body && response.body.message)
                            || 'Error when communicating the server. Try again in a while.'; // i18n
                        const details = (response.body && response.body.details) || {};
                        if (details.propertyPath && message.indexOf('{propertyPath}') === -1) {
                            message += ' ' + i18n.global.t('Field: {propertyPath}.', details);
                        }
                        errorNotification(i18n.global.t('Error'), i18n.global.t(message, details));
                    }
                }
            }
        });
    };
}

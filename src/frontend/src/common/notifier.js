import {alert, defaultModules} from '@pnotify/core';
import '@pnotify/core/dist/PNotify.css';
import * as PNotifyMobile from '@pnotify/mobile';
import '@pnotify/mobile/dist/PNotifyMobile.css';
import * as PNotifyBootstrap3 from '@pnotify/bootstrap3';
import '@pnotify/core/dist/BrightTheme.css';

defaultModules.set(PNotifyMobile, {});
defaultModules.set(PNotifyBootstrap3, {});

function showNotification(title, text, type, vue = null) {
    if (vue) {
        title = vue.$t(title);
        text = vue.$t(text);
    }
    return alert({title, text, type});
}

export function successNotification(title, text = '', vue = null) {
    return showNotification(title, text, 'success', vue);
}

export function warningNotification(title, text = '', vue = null) {
    return showNotification(title, text, 'notice', vue);
}

export function errorNotification(title, text = '', vue = null) {
    return showNotification(title, text, 'error', vue);
}

export function infoNotification(title, text = '', vue = null) {
    return showNotification(title, text, 'info', vue);
}

import {alert, defaultModules} from '@pnotify/core';
import '@pnotify/core/dist/PNotify.css';
import * as PNotifyMobile from '@pnotify/mobile';
import '@pnotify/mobile/dist/PNotifyMobile.css';
import * as PNotifyBootstrap3 from '@pnotify/bootstrap3';
import '@pnotify/core/dist/BrightTheme.css';
import {i18n} from "@/locale";

defaultModules.set(PNotifyMobile, {});
defaultModules.set(PNotifyBootstrap3, {});

function showNotification(title, text, type) {
    title = i18n.global.t(title);
    text = i18n.global.t(text);
    return alert({title, text, type});
}

export function successNotification(title, text = '') {
    return showNotification(title, text, 'success');
}

export function warningNotification(title, text = '') {
    return showNotification(title, text, 'notice');
}

export function errorNotification(title, text = '') {
    return showNotification(title, text, 'error');
}

export function infoNotification(title, text = '') {
    return showNotification(title, text, 'info');
}

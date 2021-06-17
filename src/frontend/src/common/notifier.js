import PNotify from "pnotify";
import "pnotify/dist/pnotify.buttons";
import "pnotify/dist/pnotify.buttons.css";
import "pnotify/dist/pnotify.mobile";
import "pnotify/dist/pnotify.mobile.css";
import "pnotify/dist/pnotify.css";

function showNotification(title, text, type, vue = null) {
    if (vue) {
        title = vue.$t(title);
        text = vue.$t(text);
    }
    return new PNotify({title, text, type});
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

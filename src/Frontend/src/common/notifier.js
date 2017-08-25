import PNotify from "expose-loader?PNotify!pnotify"; // TODO remove expose loader when all legacy notification are removed
import "pnotify/dist/pnotify.buttons";
import "pnotify/dist/pnotify.buttons.css";
import "pnotify/dist/pnotify.mobile";
import "pnotify/dist/pnotify.mobile.css";
import "pnotify/dist/pnotify.css";

function showNotification(title, text, type) {
    return new PNotify({title, text, type});
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

import PNotify from "expose-loader?PNotify!pnotify"; // TODO remove expose loader when all legacy notification are removed
import "pnotify/dist/pnotify.buttons";
import "pnotify/dist/pnotify.buttons.css";
import "pnotify/dist/pnotify.mobile";
import "pnotify/dist/pnotify.mobile.css";
import "pnotify/dist/pnotify.css";

function showNotification(title, text, type, icon) {
    return new PNotify({title, text, type, icon});
}

export function successNotification(title, text = '') {
    return showNotification(title, text, 'success', 'pe-7s-simple-check');
}

export function warningNotification(title, text = '') {
    return showNotification(title, text, 'notice', 'pe-7s-close-circle');
}

export function errorNotification(title, text = '') {
    return showNotification(title, text, 'error', 'pe-7s-attention');
}

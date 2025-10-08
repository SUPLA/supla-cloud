import Vue3Toastify, {toast} from 'vue3-toastify';
import {i18n} from '@/locale';
import 'vue3-toastify/dist/index.css';

export function registerNotifier(app) {
  app.use(Vue3Toastify, {
    autoClose: 5000,
  });
}

function showNotification(text, type) {
  text = i18n.global.t(text);
  toast(text, {type});
}

export function successNotification(title, text = '') {
  return showNotification(text, 'success');
}

export function warningNotification(title, text = '') {
  return showNotification(text, 'warning');
}

export function errorNotification(title, text = '') {
  return showNotification(text, 'error');
}

export function infoNotification(title, text = '') {
  return showNotification(text, 'info');
}

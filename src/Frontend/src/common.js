import 'expose-loader?jQuery!expose-loader?$!jquery';
import 'expose-loader?moment!moment';
import Vue from 'vue'
import Vuex from 'vuex'
import VueI18N from 'vue-i18n'
import VueResource from 'vue-resource'
import * as translations from './translations';
import 'moment-timezone';

Vue.use(Vuex);
Vue.use(VueI18N);
Vue.use(VueResource);

Vue.config.external = window.FRONTEND_CONFIG || {};
Vue.config.lang = Vue.config.external.locale || 'en';
Vue.config.missingHandler = (lang, key) => key;
Vue.http.options.root = (Vue.config.external.baseUrl || '').replace(/\/$/, '');
Object.keys(translations).forEach(function (lang) {
    Vue.locale(lang, translations[lang])
});

if (!Vue.config.external.timezone && moment.tz && moment.tz.guess) {
    Vue.config.external.timezone = moment.tz.guess();
    Vue.http.put('account/user-timezone', {timezone: Vue.config.external.timezone})
}

moment.locale(Vue.config.external.locale);
moment.tz.setDefault(Vue.config.external.timezone);

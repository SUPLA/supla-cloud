import Vue from 'vue'
import Vuex from 'vuex'
import VueI18N from 'vue-i18n'
import * as translations from './translations';

Vue.use(Vuex);
Vue.use(VueI18N);

Vue.config.lang = window.LOCALE || 'en';
Vue.config.missingHandler = (lang, key) => key;
Object.keys(translations).forEach(function (lang) {
    Vue.locale(lang, translations[lang])
});

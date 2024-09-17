import Vue from "vue";
import VueI18N from "vue-i18n";
import {Settings} from "luxon";

Vue.config.availableLanguages = [
    {value: 'en', text: 'English'},
    {value: 'pl', text: 'Polski'},
    {value: 'uk', text: 'Українська'},
    {value: 'cs', text: 'Čeština'},
    {value: 'de', text: 'Deutsch'},
    {value: 'es', text: 'Español'},
    {value: 'el', text: 'Ελληνικά'},
    {value: 'fr', text: 'Français'},
    {value: 'it', text: 'Italiano'},
    {value: 'lt', text: 'Lietuvių'},
    {value: 'nl', text: 'Nederlands'},
    {value: 'nb', text: 'Norsk'},
    {value: 'pt', text: 'Português'},
    {value: 'sk', text: 'Slovenčina'},
    {value: 'sl', text: 'Slovenščina'},
    {value: 'ro', text: 'Română'},
    {value: 'hu', text: 'Magyar'},
    {value: 'ar', text: 'العربية'},
    {value: 'vi', text: 'Tiếng Việt'}
];

Vue.use(VueI18N);

const i18n = new VueI18N({
    fallbackLocale: 'en',
    silentFallbackWarn: true,
    silentTranslationWarn: true,
    formatFallbackMessages: true,
});

Vue.prototype.$setLocale = (lang) => {
    if (i18n.locale !== lang) {
        Promise.resolve(loadedLanguages.includes(lang) ? true : loadLanguage(lang)).then(() => {
            i18n.locale = lang;
            if (['ar'].includes(lang)) {
                document.getElementsByTagName("html")[0].setAttribute('dir', 'rtl');
            } else {
                document.getElementsByTagName("html")[0].removeAttribute('dir');
            }
            Settings.defaultLocale = lang;
            if (Vue.prototype.$user.userData && Vue.prototype.$user.userData.locale != lang) {
                Vue.prototype.$updateUserLocale(lang);
            }
            if (Vue.prototype.$localStorage) {
                Vue.prototype.$localStorage.set('locale', lang);
            }
        });
    }
};

Vue.prototype.$updateUserLocale = (lang) => {
    return Vue.http.patch('users/current', {locale: lang, action: 'change:userLocale'}).then(() => {
        Vue.prototype.$user.userData.locale = lang;
    });
};

const loadedLanguages = [];

const loadLanguage = (lang) => {
    return import(`../../SuplaBundle/Resources/translations/messages.${lang}.yml`)
        .then((translations) => {
            i18n.setLocaleMessage(lang, translations.default);
            loadedLanguages.push(lang);
        })
        .catch(() => Vue.prototype.$setLocale('en'));
};

loadLanguage('en');

const setGuiLocale = (userData) => {
    let locale;
    let match = window.location.href.match(/[?&]lang=([a-z][a-z])/);
    if (match) {
        locale = match[1].substring(0, 2);
    } else if (userData && userData.locale) {
        locale = userData.locale;
    } else if (Vue.prototype.$localStorage && Vue.prototype.$localStorage.get('locale')) {
        locale = Vue.prototype.$localStorage.get('locale');
    } else {
        let language = window.navigator.userLanguage || window.navigator.language || 'en';
        locale = language.substring(0, 2);
    }
    const availableLocales = Vue.config.availableLanguages.map(l => l.value);
    if (!availableLocales.includes(locale)) {
        locale = 'en';
    }
    return Vue.prototype.$setLocale(locale);
};

export {
    i18n,
    loadLanguage,
    setGuiLocale
};

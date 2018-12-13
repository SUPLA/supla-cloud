import Vue from "vue";
import VueI18N from "vue-i18n";

Vue.config.availableLanguages = [
    {value: 'en', text: 'English'},
    {value: 'pl', text: 'Polski'},
    {value: 'cs', text: 'Čeština'},
    {value: 'sk', text: 'Slovenčina'},
    {value: 'lt', text: 'Lietuvių'},
    {value: 'ru', text: 'Русский'},
    {value: 'de', text: 'Deutsch'},
    {value: 'it', text: 'Italiano'},
    {value: 'pt', text: 'Português'},
    {value: 'es', text: 'Español'},
    {value: 'fr', text: 'Français'}
];

Vue.use(VueI18N);

const i18n = new VueI18N({
    // uncomment to collect missing translations automatically in MISSING_TRANSLATIONS global variable.
    // then collect yml with: Object.keys(MISSING_TRANSLATIONS).map(t => `${t}: ~`).join("\n")
    // missing(locale, key) {
    //     if (!window.MISSING_TRANSLATIONS) {
    //         window.MISSING_TRANSLATIONS = {};
    //     }
    //     window.MISSING_TRANSLATIONS[key] = '~';
    //     return key;
    // }
});

Vue.prototype.$setLocale = (lang) => {
    if (i18n.locale !== lang) {
        Promise.resolve(loadedLanguages.includes(lang) ? true : loadLanguage(lang)).then(() => {
            i18n.locale = lang;
            moment.locale(lang);
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
    return import(`json-loader!yaml-loader!../../SuplaBundle/Resources/translations/messages.${lang}.yml`)
        .then((translations) => {
            i18n.setLocaleMessage(lang, translations);
            loadedLanguages.push(lang);
        })
        .catch(() => Vue.prototype.$setLocale('en'));
};

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
    return Vue.prototype.$setLocale(locale);
};

export {
    i18n,
    loadLanguage,
    setGuiLocale
};

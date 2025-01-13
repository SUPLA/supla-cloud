import Vue from "vue";
import {createI18n} from "vue-i18n";
import {Settings} from "luxon";
import {useCurrentUserStore} from "@/stores/current-user-store";

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

const i18n = createI18n({
    legacy: false,
    fallbackLocale: 'en',
    fallbackWarn: false,
    missingWarn: false,
    fallbackFormat: true,
});

const loadedLanguages = [];

const loadLanguage = (lang) => {
    return import(`json-loader!yaml-loader!../../SuplaBundle/Resources/translations/messages.${lang}.yml`)
        .then((translations) => {
            i18n.setLocaleMessage(lang, translations.default);
            loadedLanguages.push(lang);
        })
        .catch(() => setGuiLocale('en'));
};

const detectGuiLocale = () => {
    const userLocale = useCurrentUserStore().userData?.locale;
    let locale;
    let match = window.location.href.match(/[?&]lang=([a-z][a-z])/);
    if (match) {
        locale = match[1].substring(0, 2);
    } else if (userLocale) {
        locale = userLocale;
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
    return setGuiLocale(locale);
};

const setGuiLocale = (lang) => {
    if (i18n.locale !== lang) {
        Promise.resolve(loadedLanguages.includes(lang) ? true : loadLanguage(lang)).then(() => {
            i18n.locale = lang;
            if (['ar'].includes(lang)) {
                document.getElementsByTagName("html")[0].setAttribute('dir', 'rtl');
            } else {
                document.getElementsByTagName("html")[0].removeAttribute('dir');
            }
            Settings.defaultLocale = lang;
            const currentUser = useCurrentUserStore();
            if (currentUser.username && currentUser.userData?.locale !== lang) {
                currentUser.updateUserLocale(lang);
            }
            if (Vue.prototype.$localStorage) {
                Vue.prototype.$localStorage.set('locale', lang);
            }
        });
    }
};

const escapeI18n = (text) => {
    // https://vue-i18n.intlify.dev/guide/essentials/syntax#special-characters
    return text.replaceAll(/([{}@$|])/g, "{'$1'}");
}

export {
    i18n,
    loadLanguage,
    setGuiLocale,
    detectGuiLocale,
    escapeI18n,
};

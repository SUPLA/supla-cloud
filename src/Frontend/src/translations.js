import VueI18n from "vue-i18n";
import Vue from "vue";

function requireTranslations(lang) {
    /* global require */
    return require("json-loader!yaml-loader!../../SuplaBundle/Resources/translations/messages." + lang + ".yml");
}

var pl = requireTranslations("pl");
var en = requireTranslations("en");
var ru = requireTranslations("ru");
var de = requireTranslations("de");

export const i18n = new VueI18n({
    locale: Vue.config.external.locale,
    messages: {pl, en, ru, de},
});

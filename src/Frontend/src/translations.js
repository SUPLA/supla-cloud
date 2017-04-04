function requireTranslations(lang) {
    /* global require */
    return require("json-loader!yaml-loader!../../SuplaBundle/Resources/translations/messages." + lang + ".yml");
}

export var pl = requireTranslations("pl");
export var en = requireTranslations("en");
export var ru = requireTranslations("ru");
export var de = requireTranslations("de");

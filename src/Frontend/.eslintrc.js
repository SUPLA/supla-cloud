module.exports = {
    "env": {
        "node": true,
        "browser": true,
        "es6": true
    },
    "extends": "eslint:recommended",
    "globals": {
        $: true,
        moment: true,
        SUPLA_TRANSLATIONS: true,
    },
    "parser": "babel-eslint",
    "parserOptions": {
        "sourceType": "module",
        "ecmaFeatures": {
            "experimentalObjectRestSpread": true
        }
    },
    "plugins": [
        "html"
    ],
    "rules": {
        "indent": [
            "error",
            4,
            {"SwitchCase": 1}
        ],
        "semi": [
            "error",
            "always"
        ]
    }
};

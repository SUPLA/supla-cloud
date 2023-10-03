module.exports = {
    root: true,
    env: {
        node: true,
        jest: true,
        es2022: true
    },
    'extends': [
        'plugin:vue/essential',
        'eslint:recommended',
        "plugin:cypress/recommended",
    ],
    rules: {
        'no-console': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
        'no-debugger': process.env.NODE_ENV === 'production' ? 'warn' : 'off',
        "vue/html-button-has-type": [
            "error",
            {"button": true, "submit": true, "reset": true},
        ],
        "vue/multi-word-component-names": [
            "error",
            {
                "ignores": [
                    "dashboard",
                    "fa",
                    "flipper",
                    "modal",
                    "navbar",
                    "privacy",
                    "terms",
                    "toggler",
                ]
            }
        ],
        "vue/no-mutating-props": "off",
        "vue/require-prop-types": "off",
    },
    overrides: [
        {
            files: [
                '**/__tests__/*.{j,t}s?(x)',
                '**/tests/unit/**/*.spec.{j,t}s?(x)'
            ],
            env: {
                jest: true
            }
        }
    ]
}

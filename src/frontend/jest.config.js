module.exports = {
    moduleNameMapper: {
        '^.+\\.(css|scss)$': '<rootDir>/tests/unit/setup/css-stub.js'
    },
    preset: '@vue/cli-plugin-unit-jest',
    setupFiles: [
        '<rootDir>/tests/unit/setup/setup-mocks.js',
    ],
    globals: {
        FRONTEND_VERSION: "0.0.0"
    },
}

module.exports = {
    moduleNameMapper: {
        '^.+\\.(css|scss)$': '<rootDir>/tests/unit/setup/css-stub.js',
        "@/(.*)": "<rootDir>/src/$1",
    },
    moduleFileExtensions: ['js', 'ts', 'json', 'vue'],
    testEnvironment: "jsdom",
    setupFiles: [
        '<rootDir>/tests/unit/setup/setup-mocks.js',
    ],
    transform: {
        '.*\\.js$': 'babel-jest',
        ".*\\.(vue)$": "@vue/vue2-jest"
    },
}

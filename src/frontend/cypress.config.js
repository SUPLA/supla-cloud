const {defineConfig} = require('cypress')

module.exports = defineConfig({
    fixturesFolder: 'tests/e2e/fixtures',
    screenshotsFolder: 'tests/e2e/screenshots',
    videosFolder: 'tests/e2e/videos',
    // video: true,
    downloadsFolder: 'tests/e2e/downloads',
    env: {
        DATABASE: "mysql://root:php@localhost/supla_e2e",
    },
    e2e: {
        baseUrl: 'http://localhost:8080',
        defaultCommandTimeout: 5000,
        taskTimeout: 10000,
        pageLoadTimeout: 10000,
        setupNodeEvents(on, config) {
            require('./tests/e2e/plugins/seeder')(on, config);
            require('./tests/e2e/plugins/smtp-server')(on, config);
        },
        specPattern: 'tests/e2e/specs/**/*.cy.{js,jsx,ts,tsx}',
        supportFile: 'tests/e2e/support/index.js',
    },
})

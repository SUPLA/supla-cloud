import {defineConfig} from 'cypress'
import seeder from './tests/e2e/plugins/seeder';
import smtpServer from './tests/e2e/plugins/smtp-server.js';

export default defineConfig({
  fixturesFolder: 'tests/e2e/fixtures',
  screenshotsFolder: 'tests/e2e/screenshots',
  videosFolder: 'tests/e2e/videos',
  downloadsFolder: 'tests/e2e/downloads',
  env: {
    DATABASE: "mysql://root:php@localhost/supla_e2e",
  },
  e2e: {
    baseUrl: 'http://localhost:5173',
    defaultCommandTimeout: 10000,
    taskTimeout: 10000,
    pageLoadTimeout: 10000,
    setupNodeEvents(on, config) {
      seeder(on, config);
      smtpServer(on);
    },
    specPattern: 'tests/e2e/specs/**/*.cy.{js,jsx,ts,tsx}',
    supportFile: 'tests/e2e/support/index.js',
  },
})

import {fileURLToPath} from 'node:url';
import {configDefaults, defineConfig, mergeConfig} from 'vitest/config';
import viteConfig from './vite.config.js';

export default mergeConfig(
  viteConfig,
  defineConfig({
    // Use ESM so test-utils resolves the same Vue compatibility runtime as application components.
    resolve: {alias: {'@vue/test-utils': fileURLToPath(new URL('./node_modules/@vue/test-utils/dist/vue-test-utils.esm-bundler.mjs', import.meta.url))}},
    test: {
      globals: true,
      environment: 'jsdom',
      exclude: [...configDefaults.exclude, 'e2e/**'],
      setupFiles: ['./tests/unit/setup/setup-mocks.js'],
      server: {deps: {inline: ['@vue/test-utils', 'pinia', 'vue-i18n']}},
      root: fileURLToPath(new URL('./', import.meta.url)),
    },
  })
);

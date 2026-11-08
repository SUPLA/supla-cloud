import {fileURLToPath} from 'node:url';
import {configDefaults, defineConfig, mergeConfig} from 'vitest/config';
import viteConfig from './vite.config.js';

export default mergeConfig(
  viteConfig,
  defineConfig({
    // Keep Test Utils and components on the same Vue compat runtime instance.
    resolve: {
      alias: {'@vue/test-utils': fileURLToPath(new URL('./node_modules/@vue/test-utils/dist/vue-test-utils.esm-bundler.mjs', import.meta.url))},
    },
    test: {
      server: {deps: {inline: ['@vue/compat', '@vue/test-utils', 'vue']}},
      globals: true,
      environment: 'jsdom',
      exclude: [...configDefaults.exclude, 'e2e/**'],
      setupFiles: ['./tests/unit/setup/setup-mocks.js'],
      root: fileURLToPath(new URL('./', import.meta.url)),
    },
  })
);

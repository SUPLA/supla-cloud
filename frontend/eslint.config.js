import {defineConfig, globalIgnores} from 'eslint/config';
import globals from 'globals';
import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import pluginVitest from '@vitest/eslint-plugin';
import pluginCypress from 'eslint-plugin-cypress';
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting';

export default defineConfig([
  {
    name: 'app/files-to-lint',
    files: ['src/**/*.{js,mjs,jsx,vue}', 'test/**/*.{js,mjs,jsx,vue}'],
  },

  globalIgnores(['**/dist/**', '**/dist-ssr/**', '**/coverage/**', 'tests/unit/**']),

  {
    languageOptions: {
      globals: {
        ...globals.browser,
      },
    },
  },

  {
    files: ['scripts/*.js'],
    languageOptions: {
      globals: {
        ...globals.node,
      },
    },
  },

  js.configs.recommended,
  ...pluginVue.configs['flat/essential'],

  {
    rules: {
      'vue/no-deprecated-delete-set': 'warn',
      'vue/no-mutating-props': 'warn',
      'vue/multi-word-component-names': 'warn',
      'no-unused-vars': [
        'error',
        {
          caughtErrors: 'none',
        },
      ],
    },
  },

  {
    ...pluginVitest.configs.recommended,
    files: ['src/**/__tests__/*'],
  },

  {
    ...pluginCypress.configs.recommended,
    files: ['tests/e2e/**/*.{cy,spec}.{js,ts,jsx,tsx}', 'tests/e2e/support/**/*.{js,ts,jsx,tsx}'],
  },
  skipFormatting,
]);

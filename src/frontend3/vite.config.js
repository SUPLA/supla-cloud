import {fileURLToPath, URL} from 'node:url'

import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import ViteYaml from '@modyfi/vite-plugin-yaml';
import * as path from "node:path";

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue({
      template: {
        compilerOptions: {
          compatConfig: {
            MODE: 2,
          },
        },
      },
    }),
    ViteYaml(),
    vueDevTools(),
  ],
  server: {
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8008',
        changeOrigin: true,
      },
      '/assets': {
        target: 'http://127.0.0.1:8008',
        changeOrigin: true,
      }
    }
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      vue: '@vue/compat',
    },
  },
  define: {
    FRONTEND_VERSION: "'6.6.6'",//JSON.stringify(require('./scripts/version').version),
  },
  build: {
    chunkSizeWarningLimit: 700,
    outDir: path.resolve(__dirname, '../../web'),
    assetsDir: 'dist',
    emptyOutDir: false,
  }
})

import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue2'
import inject from "@rollup/plugin-inject";
import ViteYaml from '@modyfi/vite-plugin-yaml';

const path = require("path");

export default defineConfig({
    plugins: [
        inject({   // => that should be first under plugins array
            // $: 'jquery',
            jQuery: 'jquery',
        }),
        ViteYaml(),
        vue(),
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
        extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
        alias: {
            "@": path.resolve(__dirname, "./src"),
            'vue': 'node_modules/vue/dist/vue.esm.js'
        },
    },
    define: {
        FRONTEND_VERSION: JSON.stringify(require('./scripts/version').version),
    },
    base: process.env.NODE_ENV === 'production' && !process.env.CYPRESS
        ? '/dist/'
        : '/',
    build: {
        chunkSizeWarningLimit: 600,
        outDir: path.resolve(__dirname, '../../web/dist'),
    }
})

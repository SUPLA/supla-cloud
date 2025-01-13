const path = require('path');
const webpack = require('webpack');
// const StatsPlugin = require('stats-webpack-plugin')

module.exports = {
    lintOnSave: 'warning',
    productionSourceMap: false,
    outputDir: path.resolve(__dirname, '../../web/dist'),
    publicPath: process.env.NODE_ENV === 'production' && !process.env.CYPRESS
        ? '/dist/'
        : '/',
    devServer: {
        proxy: 'http://127.0.0.1:8008'
    },
    configureWebpack: {
        // this module contains template compiler that is required for server-rendered pages to work,
        // i.e. OAuth login form or direct links execution results
        resolve: {
            // alias: {
            //     'vue$': 'vue/dist/vue.esm.js'
            // }
        },
        plugins: [
            new webpack.ProvidePlugin({
                jQuery: 'jquery',
            }),
            new webpack.DefinePlugin({
                FRONTEND_VERSION: JSON.stringify(require('./scripts/version').version),
            }),
            // new StatsPlugin('stats.json'),
        ],
    },
    chainWebpack: (config) => {
        config.plugins.delete('prefetch');

        config.resolve.alias.set('vue', '@vue/compat');

        config.module
            .rule('vue')
            .use('vue-loader')
            .tap((options) => {
                return {
                    ...options,
                    compilerOptions: {
                        compatConfig: {
                            MODE: 2
                        }
                    }
                }
            });
    },
};

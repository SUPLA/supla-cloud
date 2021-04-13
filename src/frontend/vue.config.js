const path = require('path');
const webpack = require('webpack');

module.exports = {
    lintOnSave: false,
    outputDir: path.resolve(__dirname, '../../web/dist'),
    devServer: {
        proxy: 'http://supla.local'
    },
    configureWebpack: {
        resolve: {
            alias: {
                'vue$': 'vue/dist/vue.esm.js'
            }
        },
        plugins: [
            new webpack.ProvidePlugin({
                jQuery: 'jquery',
            }),
            new webpack.DefinePlugin({
                VERSION: JSON.stringify(process.env.RELEASE_VERSION || process.env.npm_package_version)
            })
        ],
    }
};

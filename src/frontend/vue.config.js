const path = require('path');
const webpack = require('webpack');

module.exports = {
    lintOnSave: false,
    outputDir: path.resolve(__dirname, '../../web/dist'),
    publicPath: '/dist/',
    indexPath: path.resolve(__dirname, '../../web/index.html'),
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
        ],
    }
};

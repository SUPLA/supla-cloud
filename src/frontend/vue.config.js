const path = require('path');

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
        }
    }
};

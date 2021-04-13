const path = require('path');

module.exports = {
    lintOnSave: false,
    outputDir: path.resolve(__dirname, '../../web/assets/dist'),
    publicPath: '/assets/dist/',
    indexPath: path.resolve(__dirname, '../../web/index.html'),
    devServer: {
        port: 25787,
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

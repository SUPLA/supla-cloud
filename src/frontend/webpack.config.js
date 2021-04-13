const path = require('path');
const webpack = require('webpack');

const entries = {
    'commons': './src/common.js'
};

module.exports = {
    entry: entries,
    output: {
        path: path.resolve(__dirname, '../../web/assets/dist'),
        publicPath: '/assets/dist/',
        filename: process.env.NODE_ENV === 'production' ? '[name].[chunkhash].js' : '[name].js'
    },
    plugins: [
        new webpack.optimize.CommonsChunkPlugin({
            filename: process.env.NODE_ENV === 'production' ? '[name].[chunkhash].js' : '[name].js',
            name: "commons"
        }),
        new webpack.DefinePlugin({
            VERSION: JSON.stringify(process.env.RELEASE_VERSION || process.env.npm_package_version)
        })
    ],
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                options: {
                    postcss: [require('autoprefixer')],
                    loaders: {
                        'scss': 'vue-style-loader!css-loader!sass-loader',
                    }
                }
            },
            {test: /\.css$/, loader: "style-loader!css-loader"},
            {test: /\.js$/, loader: 'babel-loader', exclude: /node_modules/},
            {
                test: /\.(png|jpg|gif|svg|woff2?|ttf|eot)$/,
                loader: 'file-loader',
                options: {
                    name: 'assets/[name].[ext]?[hash]'
                }
            }
        ]
    },
    resolve: {
        extensions: ['.js', '.vue'],
        alias: {
            '@': path.resolve(__dirname, 'src'),
            'vue$': 'vue/dist/vue.common.js',
            'jquery': 'jquery/dist/jquery',
            'moment-timezone': 'moment-timezone/builds/moment-timezone-with-data-2012-2022',
            'src': path.resolve(__dirname, 'src'),
        }
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true,
        port: 25787,
        disableHostCheck: true,
        headers: {
            "Access-Control-Allow-Origin": "*",
            "Access-Control-Allow-Methods": "GET, POST, PUT, DELETE, PATCH, OPTIONS",
            "Access-Control-Allow-Headers": "X-Requested-With, content-type, Authorization"
        }
    },
    performance: {
        hints: false
    },
    devtool: '#eval-source-map'
};

if (process.env.NODE_ENV === 'production') {
    module.exports.devtool = undefined;
    // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.optimize.UglifyJsPlugin({
            sourceMap: true,
            compress: {
                warnings: false
            }
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        }),
        function () {
            // https://webpack.github.io/docs/long-term-caching.html#get-filenames-from-stats
            this.plugin("done", function (stats) {
                var hashes = stats.toJson().assetsByChunkName;
                var phpConfig = "# Config generated automatically by running composer run-script webpack\n\nsupla:\n";
                phpConfig += '  version: ' + (require('./package.json').version) + "\n";
                phpConfig += '  version_full: ' + (process.env.RELEASE_VERSION || '~') + "\n";
                phpConfig += '  webpack_hashes:\n';
                for (var chunkName in hashes) {
                    phpConfig += `    ${chunkName}: "${hashes[chunkName]}"\n`;
                }
                require("fs").writeFileSync(
                    path.join(__dirname, "../../app/config", "config_build.yml"),
                    phpConfig);
            });
        }
    ]);
}

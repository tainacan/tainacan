const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');

const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = merge(common, {
    mode: 'development',
    devtool: 'source-map',
    plugins: [
        new BundleAnalyzerPlugin({
            openAnalyzer: false,
            analyzerMode: 'static'
        })
    ],
    resolve: {
        alias: {
            //'vue$': 'vue/dist/vue.esm' // uncomment this and comment the above to use vue dev tools (can cause type error)
            'vue$': 'vue/dist/vue.min',
            'Swiper$': 'swiper/js/swiper.min.js'
        }
    }
});

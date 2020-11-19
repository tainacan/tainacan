const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = merge(common, {
    mode: 'development',
    devtool: 'inline-source-map',
    plugins: [
        new VueLoaderPlugin(),
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

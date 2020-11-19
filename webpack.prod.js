const { merge } = require('webpack-merge');
const common = require('./webpack.common.js');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = merge(common, {
    mode: 'production',
    devtool: undefined,
    plugins: [
        // new webpack.LoaderOptionsPlugin({
        //     minimize: true
        // }),
        new VueLoaderPlugin(),
    ],
    optimization: {
        minimizer: [
            new TerserPlugin({
                parallel: true,
                sourceMap: false,
                cache: true,
                terserOptions: {
                    // We preserve function names that start with capital letters as
                    // they're _likely_ component names, and these are useful to have
                    // in tracebacks and error messages.
                    keep_fnames: /__|_x|_n|_nx/,
                    mangle: {
                        keep_fnames: /__|_x|_n|_nx/,
                    },
                    output: {
                        comments: /translators:/i,
                    },
                },
                extractComments: false,
            }),
        ]
    },
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.min',
            'Swiper$': 'swiper/js/swiper.min.js'
        }
    }
});

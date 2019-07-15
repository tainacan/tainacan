let path = require('path');
let webpack = require('webpack');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = {
    entry: {
        //dev_admin: './src/js/main.js',\
        user_search: './src/admin/js/theme-main.js',
        user_admin: './src/admin/js/main.js',
        //gutenberg_collections_carousel: './src/gutenberg-blocks/tainacan-collections/collections-carousel/index.js',
        // gutenberg_items_grid: './src/gutenberg-blocks/tainacan-items/items-grid/index.js',
        gutenberg_terms_list: './src/gutenberg-blocks/tainacan-terms/terms-list/index.js',
        gutenberg_items_list: './src/gutenberg-blocks/tainacan-items/items-list/index.js',
        gutenberg_dynamic_items_list: './src/gutenberg-blocks/tainacan-items/dynamic-items-list/index.js',
        gutenberg_dynamic_items_list_theme: './src/gutenberg-blocks/tainacan-items/dynamic-items-list/dynamic-items-list-theme.js',
        gutenberg_carousel_items_list: './src/gutenberg-blocks/tainacan-items/carousel-items-list/index.js',
        gutenberg_carousel_items_list_theme: './src/gutenberg-blocks/tainacan-items/carousel-items-list/carousel-items-list-theme.js',
        gutenberg_collections_list: './src/gutenberg-blocks/tainacan-collections/collections-list/index.js',
        gutenberg_facets_list: './src/gutenberg-blocks/tainacan-facets/facets-list/index.js',
        gutenberg_facets_list_theme: './src/gutenberg-blocks/tainacan-facets/facets-list/facets-list-theme.js'
    },
    output: {
        path: path.resolve(__dirname, './src/assets/'),
        publicPath: './src/assets/',
        filename: '[name]-components.js'
    },
    module: {
        rules: [
            {
                enforce: "pre",
                test: /\.vue$/,
                exclude: /node_modules/,
                loader: "eslint-loader",
                options: {
                    fix: false,
                },
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/
            },
            {
                test: /\.(png|jpg|jpeg|gif|eot|ttf|otf|woff|woff2|svg|svgz)(\?.+)?$/,
                loader: 'file-loader'
            },
            {
                test: /\.css$/,
                use: [
                    'vue-style-loader',
                    'css-loader',
                    'postcss-loader',
                ],
            },
            {
                test: /\.s[ac]ss$/,
                use: [
                    {
                        loader: 'style-loader',
                    },
                    {
                        loader: 'css-loader'
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            includePaths: [path.resolve(__dirname, './src/admin/scss/_variables.scss')]
                        }
                    },
                ],
            }

        ]
    },
    node: {
        fs: 'empty',
        net: 'empty',
        tls: 'empty'
    },
    performance: {
        hints: false
    },
};

// Change to true for production mode
const production = false;

if (production === true) {
    const TerserPlugin = require('terser-webpack-plugin');

    console.log(`Production: ${production}`);

    module.exports.mode = 'production';

    module.exports.devtool = '';

    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify('production')
            }
        }),
        new TerserPlugin({
            parallel: true,
            sourceMap: false
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        }),
        new VueLoaderPlugin(),
    ]);

    module.exports.resolve = {
        alias: {
            'vue$': 'vue/dist/vue.min',
            'swiper$': 'swiper/dist/js/swiper.min.js'
        }
    }
} else {
    console.log(`Production: ${production}`);

    module.exports.devtool = '';

    module.exports.plugins = [
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify('development')
            },
        }),
        new VueLoaderPlugin(),
        new BundleAnalyzerPlugin({
            openAnalyzer: false,
            analyzerMode: 'static'
        })
    ];

    module.exports.resolve = {
        alias: {
            //'vue$': 'vue/dist/vue.esm' // uncomment this and comment the above to use vue dev tools (can cause type error)
            'vue$': 'vue/dist/vue.min',
            'swiper$': 'swiper/dist/js/swiper.min.js'
        }
    }
}
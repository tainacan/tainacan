let path = require('path');
let webpack = require('webpack');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = {
    entry: {
        admin: './src/views/admin/js/main.js',
        theme_search: './src/views/theme-search/js/theme-main.js',
        roles: './src/views/roles/js/roles-main.js',

        block_terms_list: './src/views/gutenberg-blocks/tainacan-terms/terms-list/index.js',
        
        block_items_list: './src/views/gutenberg-blocks/tainacan-items/items-list/index.js',
        
        block_dynamic_items_list: './src/views/gutenberg-blocks/tainacan-items/dynamic-items-list/index.js',
        block_dynamic_items_list_theme: './src/views/gutenberg-blocks/tainacan-items/dynamic-items-list/dynamic-items-list-theme.js',
        
        block_carousel_items_list: './src/views/gutenberg-blocks/tainacan-items/carousel-items-list/index.js',
        block_carousel_items_list_theme: './src/views/gutenberg-blocks/tainacan-items/carousel-items-list/carousel-items-list-theme.js',
        
        block_search_bar: './src/views/gutenberg-blocks/tainacan-items/search-bar/index.js',
        block_search_bar_script: './src/views/gutenberg-blocks/tainacan-items/search-bar/search-bar-theme-script.js',
        
        block_collections_list: './src/views/gutenberg-blocks/tainacan-collections/collections-list/index.js',
        
        block_carousel_collections_list: './src/views/gutenberg-blocks/tainacan-collections/carousel-collections-list/index.js',
        block_carousel_collections_list_theme: './src/views/gutenberg-blocks/tainacan-collections/carousel-collections-list/carousel-collections-list-theme.js',
        
        block_facets_list: './src/views/gutenberg-blocks/tainacan-facets/facets-list/index.js',
        block_facets_list_theme: './src/views/gutenberg-blocks/tainacan-facets/facets-list/facets-list-theme.js',
        
        block_faceted_search: './src/views/gutenberg-blocks/tainacan-facets/faceted-search/index.js',
        
        block_carousel_terms_list: './src/views/gutenberg-blocks/tainacan-terms/carousel-terms-list/index.js',
        block_carousel_terms_list_theme: './src/views/gutenberg-blocks/tainacan-terms/carousel-terms-list/carousel-terms-list-theme.js',

        tainacan_blocks_category_icon: './src/views/gutenberg-blocks/tainacan-blocks-category-icon.js'
    },
    output: {
        path: path.resolve(__dirname, './src/assets/js/'),
        publicPath: './src/assets/js/',
        filename: '[name].js'
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
                exclude: /node_modules/,
                loader: 'vue-loader'
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loader: 'babel-loader',
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
                exclude: /node_modules/,
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
                            includePaths: [path.resolve(__dirname, './src/views/admin/scss/_variables.scss')]
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
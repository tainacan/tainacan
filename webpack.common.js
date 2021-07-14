let path = require('path');
const webpack = require('webpack');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

module.exports = {
    entry: {
        admin: './src/views/admin/js/main.js',
        media_component: './src/views/media-component/media-component.js',
        theme_search: './src/views/theme-search/js/theme-main.js',
        item_submission: './src/views/item-submission/js/item-submission-main.js',
        roles: './src/views/roles/js/roles-main.js',
        reports: './src/views/reports/js/reports-main.js',

        block_terms_list: './src/views/gutenberg-blocks/tainacan-blocks/terms-list/index.js',
        
        block_items_list: './src/views/gutenberg-blocks/tainacan-blocks/items-list/index.js',
        
        block_dynamic_items_list: './src/views/gutenberg-blocks/tainacan-blocks/dynamic-items-list/index.js',
        block_dynamic_items_list_theme: './src/views/gutenberg-blocks/tainacan-blocks/dynamic-items-list/dynamic-items-list-theme.js',
        
        block_carousel_items_list: './src/views/gutenberg-blocks/tainacan-blocks/carousel-items-list/index.js',
        block_carousel_items_list_theme: './src/views/gutenberg-blocks/tainacan-blocks/carousel-items-list/carousel-items-list-theme.js',
        
        block_search_bar: './src/views/gutenberg-blocks/tainacan-blocks/search-bar/index.js',
        block_search_bar_theme: './src/views/gutenberg-blocks/tainacan-blocks/search-bar/search-bar-theme.js',
        
        block_collections_list: './src/views/gutenberg-blocks/tainacan-blocks/collections-list/index.js',
        
        block_carousel_collections_list: './src/views/gutenberg-blocks/tainacan-blocks/carousel-collections-list/index.js',
        block_carousel_collections_list_theme: './src/views/gutenberg-blocks/tainacan-blocks/carousel-collections-list/carousel-collections-list-theme.js',
       
        block_carousel_related_items: './src/views/gutenberg-blocks/tainacan-blocks/carousel-related-items/index.js',

        block_facets_list: './src/views/gutenberg-blocks/tainacan-blocks/facets-list/index.js',
        block_facets_list_theme: './src/views/gutenberg-blocks/tainacan-blocks/facets-list/facets-list-theme.js',

        block_item_submission_form: './src/views/gutenberg-blocks/tainacan-blocks/item-submission-form/index.js',

        block_faceted_search: './src/views/gutenberg-blocks/tainacan-blocks/faceted-search/index.js',
        
        block_carousel_terms_list: './src/views/gutenberg-blocks/tainacan-blocks/carousel-terms-list/index.js',
        block_carousel_terms_list_theme: './src/views/gutenberg-blocks/tainacan-blocks/carousel-terms-list/carousel-terms-list-theme.js',

        tainacan_blocks_category_icon: './src/views/gutenberg-blocks/js/tainacan-blocks-category-icon.js'
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
    plugins: [
        new webpack.ProvidePlugin({
            'Swiper': 'Swiper',
            'PhotoSwipe': 'PhotoSwipe'
        }),
        new MomentLocalesPlugin({
            localesToKeep: ['en', 'en-ca', 'en-nz', 'en-gb', 'es-au', 'es-in', 'pt-br', 'pt', 'es', 'es-us', 'es-do', 'fr', 'fr-ch', 'fr-ca', 'sv'],
        }),
    ]
};
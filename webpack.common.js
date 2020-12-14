let path = require('path');

module.exports = {
    entry: {
        admin: './src/views/admin/js/main.js',
        theme_search: './src/views/theme-search/js/theme-main.js',
        item_submission: './src/views/item-submission/js/item-submission-main.js',
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

        block_item_submission_form: './src/views/gutenberg-blocks/tainacan-items/item-submission-form/index.js',

        block_faceted_search: './src/views/gutenberg-blocks/tainacan-facets/faceted-search/index.js',
        
        block_carousel_terms_list: './src/views/gutenberg-blocks/tainacan-terms/carousel-terms-list/index.js',
        block_carousel_terms_list_theme: './src/views/gutenberg-blocks/tainacan-terms/carousel-terms-list/carousel-terms-list-theme.js',

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
    }
};
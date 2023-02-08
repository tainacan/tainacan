let path = require('path');
const webpack = require('webpack');
const ESLintPlugin = require('eslint-webpack-plugin');
const { VueLoaderPlugin } = require('vue-loader');
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

module.exports = {
    entry: {
        tainacan_pages_common_scripts: './src/views/tainacan-pages-common-scripts.js',
        tainacan_blocks_common_scripts: './src/views/gutenberg-blocks/tainacan-blocks-common-scripts.js',
        tainacan_blocks_category_icon: './src/views/gutenberg-blocks/js/tainacan-blocks-category-icon.js',
        tainacan_blocks_query_variations: './src/views/gutenberg-blocks/js/tainacan-blocks-query-variations.js',

        block_terms_list: './src/views/gutenberg-blocks/blocks/terms-list/index.js',
        block_items_list: './src/views/gutenberg-blocks/blocks/items-list/index.js',
        block_dynamic_items_list: './src/views/gutenberg-blocks/blocks/dynamic-items-list/index.js',
        block_carousel_items_list: './src/views/gutenberg-blocks/blocks/carousel-items-list/index.js',
        block_search_bar: './src/views/gutenberg-blocks/blocks/search-bar/index.js',
        block_collections_list: './src/views/gutenberg-blocks/blocks/collections-list/index.js',
        block_carousel_collections_list: './src/views/gutenberg-blocks/blocks/carousel-collections-list/index.js',
        block_related_items_list: './src/views/gutenberg-blocks/blocks/related-items-list/index.js',
        block_facets_list: './src/views/gutenberg-blocks/blocks/facets-list/index.js',
        block_item_submission_form: './src/views/gutenberg-blocks/blocks/item-submission-form/index.js',
        block_faceted_search: './src/views/gutenberg-blocks/blocks/faceted-search/index.js',
        block_carousel_terms_list: './src/views/gutenberg-blocks/blocks/carousel-terms-list/index.js',
        block_item_gallery: './src/views/gutenberg-blocks/blocks/item-gallery/index.js',
        block_item_metadata_sections: './src/views/gutenberg-blocks/blocks/item-metadata-sections/index.js',
        block_item_metadata_section: './src/views/gutenberg-blocks/blocks/item-metadata-section/index.js',
        block_item_metadata: './src/views/gutenberg-blocks/blocks/item-metadata/index.js',
        block_item_metadatum: './src/views/gutenberg-blocks/blocks/item-metadatum/index.js',
        block_geocoordinate_item_metadatum: './src/views/gutenberg-blocks/blocks/geocoordinate-item-metadatum/index.js',
        block_metadata_section_name: './src/views/gutenberg-blocks/blocks/metadata-section-name/index.js',
        block_metadata_section_description: './src/views/gutenberg-blocks/blocks/metadata-section-description/index.js'
    },
    output: {
        path: path.resolve(__dirname, './src/assets/js/'),
        publicPath: './wp-content/plugins/tainacan/assets/js/',
        filename: '[name].js',
        chunkFilename: `[name].js?ver=[contenthash]`
    },
    module: {
        rules: [
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
                            sassOptions: {
                                includePaths: [path.resolve(__dirname, './src/views/admin/scss/_variables.scss')]
                            }
                        }
                    },
                ],
            }
        ]
    },
    resolve: {
        fallback: {
            fs: false,
            net: false,
            tls: false
        }
    },
    performance: {
        hints: false
    },
    plugins: [
        new webpack.DefinePlugin({
            'TAINACAN_ENV': JSON.stringify(process.env.NODE_ENV)
        }),
        new VueLoaderPlugin({
            prettify: false
        }),
        new webpack.ProvidePlugin({
            'PhotoSwipe': 'PhotoSwipe'
        }),
        new MomentLocalesPlugin({
            localesToKeep: ['en', 'en-ca', 'en-nz', 'en-gb', 'es-au', 'el', 'es-in', 'pt-br', 'pt', 'ca', 'es', 'es-us', 'es-mx', 'es-do', 'fr', 'fr-ch', 'fr-ca', 'sv', 'sq', 'sk', 'uk'],
        }),
        new ESLintPlugin({
            extensions: ['vue'],
            exclude: ['/node_modules/']
        })
    ],
    stats: {
        errorDetails: true,
        children: true
    }
};
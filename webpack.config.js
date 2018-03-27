let path = require('path');
let webpack = require('webpack');

module.exports = {
    entry:  {
       dev_admin: './src/js/main.js',
       user_admin: './src/admin/js/main.js'
    },
    output: {
        path: path.resolve(__dirname, './src/assets/'),
        publicPath: './src/assets/',
        filename: '[name]-components.js'
    },
    devtool: 'eval-source-map',
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
                loader: 'vue-loader',
                options: {
                    // vue-loader options go here
                }
            },
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/
            },
            {
                test: /\.(png|jpg|jpeg|gif|eot|ttf|woff|woff2|svg|svgz)(\?.+)?$/,
                loader: 'file-loader'
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader', 'postcss-loader', 'sass-loader'],
            },
            {
                test: /\.scss$/,
                loader: 'sass-resources-loader',
                options: {
                    resources: path.resolve(__dirname, './src/admin/scss/_variables.scss')
                }
            }

        ]
    },
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.js'
        }
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true
    },
    node: {
        fs: 'empty'
    },
    performance: {
        hints: false
    },
};

if (process.env.NODE_ENV === 'production') {
    module.exports.devtool = 'inline-source-map';
    // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        // new webpack.optimize.UglifyJsPlugin({
        //     compress: {
        //         warnings: false
        //     }
        // }),
        // new webpack.LoaderOptionsPlugin({
        //     minimize: true
        // })
    ]);
    // module.exports.resolve.alias = {
    //     'vue$': 'vue/dist/vue.min.js'
    // }
}
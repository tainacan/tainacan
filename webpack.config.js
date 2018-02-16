var path = require('path')
var webpack = require('webpack')

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
                use: ['style-loader', 'css-loader', 'postcss-loader'],
            }
        ]
    },
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue'
        }
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true
    },
}

if (process.env.NODE_ENV === 'production') {
    module.exports.devtool = 'inline-source-map';
    // http://vue-loader.vuejs.org/en/workflow/production.html
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.optimize.UglifyJsPlugin({
            compress: {
                warnings: false
            }
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        })
    ])
}
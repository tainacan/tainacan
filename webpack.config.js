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
    node: {
        fs: 'empty',
        net: 'empty',
        tls: 'empty'
    },
    performance: {
        hints: false
    },
};

// Change to false for development mode
const production = false;

if (production === true) {
    const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

    console.log({'production': production});

    module.exports.devtool = '';

    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify('production')
            }
        }),
        new UglifyJsPlugin({
            parallel: true,
            sourceMap: false
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        })
    ]);

    module.exports.resolve = {
        alias: {
            'vue$': 'vue/dist/vue.min'
        }
    }
} else {
    console.log({'production': production});

    module.exports.devtool = '';

    module.exports.plugins = [
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: JSON.stringify('development')
            },
        }),
    ];

    module.exports.mode = 'development';

    module.exports.resolve = {
        alias: {
            //'vue$': 'vue/dist/vue.esm' // uncomment this and comment the above to use vue dev tools (can cause type error)
            'vue$': 'vue/dist/vue.min'
        }
    }
}
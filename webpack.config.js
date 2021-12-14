const path = require('path');
const webpack = require('webpack');

module.exports = {
    mode: "development",
    watch: true,
    watchOptions: {
        aggregateTimeout: 200,
        poll: 1000,
        ignored: /node_modules/,
    },
    entry: {
        public: './public.js',
        private: './private.js'
    },
    output: {
        filename: '[name].bundle.js',
        path: path.resolve(__dirname, 'dist'),
        clean: true
    },
    plugins: [
        new webpack.ProvidePlugin({
            "$": "jquery",
            "jQuery": "jquery",
            "window.jQuery": "jquery"
        }),
    ],
    resolve: {
        alias: {
            "font-awesome": "font-awesome/css/font-awesome.css",
            "pure": "purecss/build/pure.css",
            "base": "purecss/build/base.css",
            "grids": "purecss/build/grids.css",
            "grids-responsive": "purecss/build/grids-responsive.css",
            "jscolor": "@eastdesire/jscolor/jscolor.js",
            "jquery-ui-slider": "jquery-ui-slider/jquery-ui.js",
            "jquery.cookie": "jquery.cookie/jquery.cookie.js",
            "chart": "chart.js/dist/chart.js"
        },
    },
    module: {
        rules: [{
                test: /\.css$/,
                use: [
                    'style-loader',
                    'css-loader',
                ],
            }, {
                test: /\.(png|svg|jpg|gif)$/,
                use: [
                    'file-loader',
                ],
            }, {
                test: /\.(woff|woff2|eot|ttf|otf)$/,
                use: [
                    'file-loader',
                ],
            }, {
                test: /\.(csv|tsv)$/,
                use: [
                    'csv-loader',
                ],
            },
            {
                test: /\.xml$/,
                use: [
                    'xml-loader',
                ],
            },
        ],
    },
};
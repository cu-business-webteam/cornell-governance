const path = require('path');
const webpack = require('webpack');

module.exports = {
    mode: "production",
    entry: {
        'cornell-governance': './src/js/cornell-governance.js',
        'cornell-governance-admin': './src/js/cornell-governance-admin.js',
        'cornell-governance/charts': './src/js/cornell-governance/charts.js'
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'dist/js'),
    },
    optimization: {
        minimize: false
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', {targets: 'defaults'}]
                        ]
                    }
                }
            }
        ]
    }
};
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const dotenv = require('dotenv');
const webpack = require('webpack');


const OUTPUT_PATH = process.env.OUTPUT_PATH || 'dist';
module.exports = (env, argv) => {
    const isProduction = argv.mode === 'production';
    const envFile = env && env.environment ? `.env.${env.environment}` : '.env';
    const envVars = dotenv.config({ path: envFile }).parsed;

    const envKeys = Object.keys(envVars).reduce((prev, next) => {
        prev[`process.env.${next}`] = JSON.stringify(envVars[next]);
        return prev;
    }, {});

    const plugins = isProduction
        ? [
              new MiniCssExtractPlugin({
                  filename: 'css/[name].css'
              })
          ]
        : [];

    return {
        mode: isProduction ? 'production' : 'development',
        entry: {
            index: ['./src/index.js', './src/design/design.scss']
        },
        output: {
            path: path.resolve(__dirname, OUTPUT_PATH),
            filename: 'js/[name].js'
        },
        module: {
            rules: [
                {
                    test: /\.scss$/,
                    use: [
                        isProduction ? MiniCssExtractPlugin.loader : 'style-loader',
                        'css-loader',
                        {
                            loader: 'sass-loader',
                            options: {
                                sourceMap: !isProduction
                            }
                        }
                    ]
                },
                {
                    test: /\.(woff(2)?|ttf|eot|svg)(\?v=\d+\.\d+\.\d+)?$/,
                    type: 'asset/resource',
                    generator: {
                        filename: 'fonts/[name][ext]'
                    }
                }
            ]
        },
        plugins: [
            ...plugins,
            new HtmlWebpackPlugin({
                template: path.resolve(__dirname, 'src/index.html'),
                filename: 'index.html',
                inject: 'body',
                templateParameters: envKeys
            }),
            new CopyWebpackPlugin({
                patterns: [
                    {
                        from: path.resolve(__dirname, 'src/design/assets/images/favicon.ico'),
                        to: 'favicon.ico',
                        toType: 'template',
                        noErrorOnMissing: true
                    }
                ]
            }),
            new webpack.DefinePlugin(envKeys)
        ],
        resolve: {
            extensions: ['.scss', '.css', '.html', '.js']
        },
        devServer: {
            port: 9001,
            compress: true,
            hot: true,
            historyApiFallback: true
        }
    };
};

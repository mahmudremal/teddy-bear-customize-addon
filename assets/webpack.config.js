/**
 * Webpack configuration.
 */
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCssAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const cssnano = require('cssnano'); // https://cssnano.co/
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin'); // https://webpack.js.org/plugins/copy-webpack-plugin/
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
const TerserPlugin = require("terser-webpack-plugin");

// JS Directory path.
const JS_DIR = path.resolve(__dirname, 'src/js');
const IMG_DIR = path.resolve(__dirname, 'src/img');
const LIB_DIR = path.resolve(__dirname, 'src/library');
const BUILD_DIR = path.resolve(__dirname, 'build');
const SRC_DIR = path.resolve(__dirname, 'src');

const entry = {
    public       : JS_DIR + '/public.js',
    admin        : JS_DIR + '/admin.js',
    popup        : JS_DIR + '/popup/index.js',
    // recorder : JS_DIR + '/recorder.js',
    // chat     : JS_DIR + '/chat.js',
};

const output = {
    path: BUILD_DIR,
    filename: 'js/[name].js',
};

const plugins = (argv) => [
    new CleanWebpackPlugin({
        cleanStaleWebpackAssets: ('production' === argv.mode ) // Automatically remove all unused webpack assets on rebuild, when set to true in production. (https://www.npmjs.com/package/clean-webpack-plugin#options-and-defaults-optional)
    }),
    new MiniCssExtractPlugin({
        filename: 'css/[name].css'
    }),
    new CopyPlugin({
        patterns: [
            {from: LIB_DIR, to: BUILD_DIR + '/library'},
            {from: SRC_DIR + '/icons', to: BUILD_DIR + '/icons'},
        ]
    }),
];

const rules = [
    {
        test: /\.js$/,
        include: [ JS_DIR ],
        exclude: /node_modules/,
        use: 'babel-loader'
    },
    {
        test: /\.(css|scss)$/,
        exclude: /node_modules/,
        use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            'sass-loader',
        ]
    },
    {
        test: /\.(png|jpg|svg|jpeg|gif|ico)$/,
        use: {
            loader: 'file-loader',
            options: {
                name: '[path][name].[ext]',
                publicPath: 'production' === process.env.NODE_ENV ? '../' : '../../'
            }
        }
    },
    {
        test: /\.(ttf|otf|eot|svg|woff(2)?)(\?[a-z0-9]+)?$/,
        exclude: [ IMG_DIR, /node_modules/ ],
        use: {
            loader: 'file-loader',
            options: {
                name: '[path][name].[ext]',
                publicPath: 'production' === process.env.NODE_ENV ? '../' : '../../'
            }
        }
    },
    {
        test: /\.twig$/,
        exclude: [ IMG_DIR, /node_modules/ ],
        use: {
            loader: 'twig-loader',
        }
    },
    {
        test: /\.txt$/i,
        use: [
          {
            loader: 'raw-loader',
            options: {
              esModule: false,
            },
          },
        ],
    },
];

module.exports = (env, argv) => ({
    entry: entry,
    output: output,
    devtool: 'source-map',
    module: {
        rules: rules,
    },
    optimization: {
        minimizer: [
            new OptimizeCssAssetsPlugin({
                cssProcessor: cssnano
            }),
            new TerserPlugin({
                parallel: true,
                sourceMap: false,
                extractComments: true,
                minify: TerserPlugin.uglifyJsMinify,
                terserOptions: {ecma: 6}
            })
        ]
    },
    plugins: [
        ...plugins(argv),
        new DependencyExtractionWebpackPlugin({
            injectPolyfill: true,
            combineAssets: true,
            useDefaults: true
        })
    ],
    externals: {
        jquery: 'jQuery',
        react: 'React',
        'react-dom': 'ReactDOM',
        'react-dom/client': 'ReactDOM'
    },
    watchOptions: {
        aggregateTimeout: 300,
        poll: 1000,
        ignored: /node_modules|build/,
    },
});

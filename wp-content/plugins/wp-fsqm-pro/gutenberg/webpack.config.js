// Webpack config for building gutenberg
// Load webpack for use of certain webpack tools and methods
const webpack = require('webpack');
// For extracting CSS (and SASS) into separate files
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
// Optimizers
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const WriteFilePlugin = require('write-file-webpack-plugin');

// path
const path = require('path');

// Is it devMode?
const devMode = process.env.NODE_ENV !== 'production';

// Define WordPress dependencies
const externals = {
	react: 'React',
	'react-dom': 'ReactDOM',
};

// Main CSS loader for everything but blocks..
const cssExtractTextPlugin = new MiniCssExtractPlugin({
	// Extracts CSS into a build folder inside the directory current directory
	filename: 'eform-gutenberg-style.css',
});
const autoprefixer = require('autoprefixer');

const wpDependencies = ['components', 'element', 'blocks', 'i18n', 'editor'];
// Setup externals for all WordPress dependencies
wpDependencies.forEach(wpDependency => {
	externals[`@wordpress/${wpDependency}`] = {
		this: ['wp', wpDependency],
	};
});

const config = {
	entry: {
		app: [path.join(__dirname, 'src/index.js')],
	},
	output: {
		filename: 'eform-gutenberg.js',
		path: path.join(__dirname, 'dist'),
		library: ['eform', '[name]'],
		libraryTarget: 'this',
		publicPath: devMode
			? 'http://localhost:3000/wp-content/plugins/wp-fsqm-pro/gutenberg/dist/'
			: '',
	},
	externals,
	module: {
		rules: [
			// Run JavaScript files through Babel
			{
				test: /\.jsx?$/,
				exclude: /node_modules/,
				use: 'babel-loader',
			},
			// Setup SASS (and CSS) to be extracted
			{
				test: /\.(sa|sc|c)ss$/,
				use: [
					devMode ? 'style-loader' : MiniCssExtractPlugin.loader,
					'css-loader',
					{
						loader: 'postcss-loader',
						options: {
							plugins: [autoprefixer],
						},
					},
					{
						loader: 'sass-loader',
						options: {
							// Prefer `dart-sass`, even if `sass-embedded` is available
							implementation: require("sass"),
						},
					}
				],
			},
		],
	},
	resolve: {
		modules: ['node_modules', path.join(__dirname, 'src')],
		extensions: ['.js', '.jsx'],
		alias: {
			Blocks: path.join(__dirname, 'src/Blocks'),
			Components: path.join(__dirname, 'src/Components'),
			Utils: path.join(__dirname, 'src/Utils'),
			Edits: path.join(__dirname, 'src/Edits'),
		},
	},
	plugins: [
		// Setup environment conditions
		new webpack.DefinePlugin({
			'process.env.NODE_ENV': JSON.stringify(
				process.env.NODE_ENV || 'development'
			),
		}),
		// Pull in cssExtractTextPlugin settings
		cssExtractTextPlugin,
	],
	devtool: 'source-map',
};

if (devMode) {
	// Apply source mapping when not in production
	config.mode = 'development';
	// Add custom dev server
	const overlayStyles = {
		zIndex: 9999999,
		fontSize: '14px',
		fontFamily:
			'Dank Mono, Operator Mono SSm, Operator Mono, Menlo, Consolas, monospace',
		padding: '32px 16px',
	};
	// HotClient needed for development environment
	const webpackHotClient = `webpack-hot-middleware/client?overlay=true&reload=true&overlayStyles=${encodeURIComponent(
		JSON.stringify(overlayStyles)
	)}`;
	config.entry.app.push(webpackHotClient);
	config.plugins.push(new webpack.HotModuleReplacementPlugin());
	config.plugins.push(new webpack.NoEmitOnErrorsPlugin());
	config.plugins.push(new WriteFilePlugin());
} else {
	// Minify JavaScript & CSS when in production
	config.optimization = {
		minimizer: [
			new UglifyJsPlugin({
				cache: true,
				parallel: true,
				sourceMap: true, // set to true if you want JS source maps
			}),
			new OptimizeCSSAssetsPlugin({}),
		],
	};
	config.mode = 'production';
}

module.exports = config;

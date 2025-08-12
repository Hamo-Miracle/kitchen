/* eslint-disable import/no-extraneous-dependencies */
const browserSync = require('browser-sync').create();
// const chalkPipeInquirer = require('chalk-pipe');

// Create a webpack instance, but don't run it yet
const webpack = require('webpack');

const webpackDevMiddleware = require('webpack-dev-middleware');
const webpackHotMiddleware = require('webpack-hot-middleware');

// Get the function for creating varying config
const webpackConfig = require('./webpack.config');

let middleware = [];
const webpackBundler = webpack(webpackConfig);
const devMiddleware = webpackDevMiddleware(webpackBundler, {
	noInfo: true,
	publicPath: webpackConfig.output.publicPath,
	stats: { colors: true },
});
const devHotMiddleWare = webpackHotMiddleware(webpackBundler);
middleware = [devMiddleware, devHotMiddleWare];

browserSync.init({
	logLevel: 'info',
	port: 3000,
	ui: {
		port: 3001,
		weinre: {
			port: 8080,
		},
	},
	debugInfo: true,
	proxy: {
		target: 'http://eform.test',
		// Middleware for webpack hot reload
		middleware,
	},
	host: 'localhost',
	open: false,
	notify: false,
	background: false,
});

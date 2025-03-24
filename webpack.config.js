const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );


module.exports = {
	...defaultConfig,
	...{
		entry: {
			'cno-plugin': __dirname + `/src/index.ts`,
		},
		resolve: {
			...defaultConfig.resolve,
			extensions: [ '.js', '.jsx', '.ts', '.tsx' ],
		},
		output: {
			path: __dirname + `/dist`,
			filename: `[name].js`,
		},
		plugins: [
			...defaultConfig.plugins,
			new RemoveEmptyScriptsPlugin( {
				stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS
			} )
		]
	},
};

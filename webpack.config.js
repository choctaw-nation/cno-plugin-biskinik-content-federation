const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );

module.exports = {
	...defaultConfig,
	...{
		entry: {
			'cno-plugin-biskinik-content-federation':
				__dirname + `/src/index.tsx`,
		},
		resolve: {
			...defaultConfig.resolve,
			extensions: [ '.js', '.jsx', '.ts', '.tsx' ],
		},
		output: {
			path: __dirname + `/build`,
			filename: `[name].js`,
		},
		plugins: [
			...defaultConfig.plugins,
			new RemoveEmptyScriptsPlugin( {
				stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
			} ),
		],
	},
};

/**
 * Harvard Arnold Arboretum Plugin
 * Registers the Origin custom taxonomy
 *
 * @package
 * @subpackage Harvard_Arnold_Arboretum
 * @since 1.0.0
 */

/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		...defaultConfig.entry(),
		'film-properties/index': './src/components/film-properties.js',
	},
};

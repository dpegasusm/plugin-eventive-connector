<?php
/**
 * Plugin Name: EventiveWP
 * Plugin URI: https://eventive.org/
 * Description: Seamlessly integrate Eventive's Event and Ticketing Services into your WordPress site. Includes dynamic event loaders, shortcode support, event bucket overrides, and Gutenberg blocks.
 * Version:           0.0.1
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            David Marshall
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Author URI:        https://eventive.org/
 * Text Domain:       eventive-wp
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// register activation and deactivation hooks.
register_activation_hook( __FILE__, 'eventive_wp_activate' );
register_deactivation_hook( __FILE__, 'eventive_wp_deactivate' );

// Get the plugin data so we can use it here to define props.
// Get the plugin data so we can use it here to define props.
$plugin_data = get_file_data(
	__FILE__,
	array(
		'Version' => 'Version',
	)
);

// Set us a definition so that we can load pdp from anywhere.
define( 'EVENTIVE_WP_PLUGIN', plugin_dir_url( __FILE__ ) );
define( 'EVENTIVE_WP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'EVENTIVE_WP_CURRENT_VERSION', ( $plugin_data && $plugin_data['Version'] ) ? $plugin_data['Version'] : '1.0.0' );

// Load the front-end functionality.
require_once EVENTIVE_WP_PLUGIN_PATH . 'includes/class-eventive-wp-block.php';
$eventive_wp_block = new Eventive_WP_Blocks();
$eventive_wp_block->init();

// Load the admin functionality.
require_once EVENTIVE_WP_PLUGIN_PATH . 'includes/class-eventive-wp-admin.php';
$eventive_wp_admin = new Eventive_WP_Admin();
$eventive_wp_admin->init();

/**
 * Run on activate to setup the plugin
 *
 * @since Harvard Arnold Arboretum 1.0
 */
function eventive_wp_activate() {
	// flush the rewrite rules in the plugin so that our new rules take effect.
	flush_rewrite_rules();
}

/**
 * Run on activate to setup the plugin
 *
 * @since Harvard Arnold Arboretum 1.0
 */
function eventive_wp_deactivate() {
	// flush the rewrite rules in the plugin so that our old rules are removed.
	flush_rewrite_rules();
}





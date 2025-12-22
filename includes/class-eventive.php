<?php
/**
 * Eventive Plugin
 *
 * @package WordPress
 * @subpackage Eventive
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dumplings_Member_List
 */
class Eventive {

	/**
	 * Init callback for register.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		// Admin init for adding a notice about the API key being needed if not there. 
		add_action( 'admin_init', array( $this, 'eventive_admin_init' ) );
	}

	/**
	 * Admin init to check for API key.
	 *
	 * @return void
	 */
	public function eventive_admin_init() {
		// Check if we have the API key set.
		$api_key = get_option( 'eventive_festival_agile_api_key', '' );

		if ( empty( $api_key ) ) {
			add_action(
				'admin_notices',
				function() {
					echo '<div class="notice notice-warning is-dismissible">
						<p><strong>Eventive:</strong> API Key is not set. Please set it in the <a href="' . esc_url( admin_url( 'options-general.php?page=eventive-film-settings' ) ) . '">settings page</a> to enable integration.</p>
					</div>';
				}
			);
		}
	}
}

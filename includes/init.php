<?php

// ====================================================
// DYNAMIC SCRIPT INCLUSION
// ====================================================
function add_eventive_dynamic_scripts() {
	// Fetch Eventive options
	$options    = get_option( 'eventive_admin_options_option_name' );
	$secret_key = $options['your_eventive_secret_key_2'] ?? '';

	// Support per-page Eventive loader override
	$default_bucket  = $options['your_eventive_event_bucket_1'] ?? '';
	$override_bucket = is_singular() ? get_post_meta( get_the_ID(), '_eventive_loader_override', true ) : '';
	$event_bucket    = $override_bucket ?: $default_bucket;

	if ( empty( $secret_key ) || empty( $event_bucket ) ) {
		return; // required config missing
	}

	// Cache the event buckets response to avoid repeat API calls per request
	$cache_key = 'eventive_buckets_' . md5( $secret_key );
	$data      = get_transient( $cache_key );

	if ( false === $data ) {
		$response = wp_remote_get(
			'https://api.eventive.org/event_buckets',
			array(
				'headers' => array( 'x-api-key' => $secret_key ),
				'timeout' => 15,
			)
		);
		if ( is_wp_error( $response ) ) {
			error_log( 'Eventive API error: ' . $response->get_error_message() );
			return;
		}
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			error_log( 'Eventive API response parsing error.' );
			return;
		}
		// cache for 10 minutes
		set_transient( $cache_key, $data, 10 * MINUTE_IN_SECONDS );
	}

	if ( empty( $data['event_buckets'] ) ) {
		error_log( 'Eventive: no event buckets found.' );
		return;
	}

	// Find the matching event bucket
	$matching_bucket = array_filter( $data['event_buckets'], fn( $bucket ) => ( $bucket['id'] ?? '' ) === $event_bucket );
	if ( empty( $matching_bucket ) ) {
		error_log( "No matching Eventive bucket found for ID: {$event_bucket}" );
		return;
	}

	$event_bucket_details = reset( $matching_bucket );
	$root_url             = $event_bucket_details['urls']['root'] ?? '';
	if ( empty( $root_url ) ) {
		error_log( 'Eventive bucket root URL is missing.' );
		return;
	}

	// Normalize
	$root_url   = rtrim( $root_url, '/' ) . '/';
	$loader_url = $root_url . 'loader.js';

	// Enqueue Stripe and Eventive loader via WP APIs (Elementor-safe)
	if ( ! wp_script_is( 'stripe-v3', 'registered' ) ) {
		wp_register_script( 'stripe-v3', 'https://js.stripe.com/v3/', array(), null, true );
	}
	wp_enqueue_script( 'stripe-v3' );

	if ( ! wp_script_is( 'eventive-loader', 'registered' ) ) {
		wp_register_script( 'eventive-loader', $loader_url, array(), null, true );
		// Defer if supported (WP 6.3+)
		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( 'eventive-loader', 'strategy', 'defer' );
		}
	}
	wp_enqueue_script( 'eventive-loader' );

	// Inline bootstrap for guarded rebuilds that play nice with Elementor
	$inline = <<<'JS'
(function(){
  if (!window.__EventiveEE) window.__EventiveEE = {};
  if (window.__EventiveEE._inlineInjected) return; // ensure we only inject once per page
  window.__EventiveEE._inlineInjected = true;

  function runRebuildOnce(){
    if (!window.Eventive) return;
    if (window.__EventiveEE._rebuilt) return;
    try { window.Eventive.rebuild(); } catch (e) {}
    window.__EventiveEE._rebuilt = true;
  }

  // DOM ready path
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function(){ setTimeout(runRebuildOnce, 300); }, { once:true });
  } else {
    setTimeout(runRebuildOnce, 300);
  }

  // Elementor: re-trigger on widget renders and popups, but keep the guard
  if (window.jQuery && window.elementorFrontend) {
    jQuery(window).on('elementor/frontend/init', function(){
      try {
        elementorFrontend.hooks.addAction('frontend/element_ready/global', function(){ setTimeout(runRebuildOnce, 0); });
        jQuery(document).on('elementor/popup/show', function(){ setTimeout(runRebuildOnce, 0); });
      } catch(e) {}
    });
  }
})();
JS;
	wp_add_inline_script( 'eventive-loader', $inline, 'after' );
}

add_action( 'enqueue_block_editor_assets', 'add_eventive_dynamic_scripts' );
add_action( 'wp_enqueue_scripts', 'add_eventive_dynamic_scripts' ); // was wp_footer

// ====================================================
// SHORTCODES INCLUSION
// ====================================================
$shortcodes_folder = plugin_dir_path( __FILE__ ) . 'shortcodes/';
$css_url           = plugin_dir_url( __FILE__ ) . 'css/';

foreach ( glob( $shortcodes_folder . '*.php' ) as $file ) {
	include_once $file;

	// Register a matching CSS file for later conditional enqueue
	$basename = basename( $file, '.php' );
	$css_path = plugin_dir_path( __FILE__ ) . "css/{$basename}.css";
	if ( file_exists( $css_path ) ) {
		$ver = filemtime( $css_path );
		wp_register_style(
			"eventive-{$basename}-style",
			$css_url . "{$basename}.css",
			array( 'eventive-mode-style' ),
			$ver
		);
	}
}

// ====================================================
// LOCALIZE API KEY AND EVENT BUCKET
// ====================================================
add_action(
	'enqueue_block_editor_assets',
	function () {
		$eventive_admin_options = get_option( 'eventive_admin_options_option_name' );
		$secret_key             = $eventive_admin_options['your_eventive_secret_key_2'] ?? '';
		$event_bucket           = $eventive_admin_options['your_eventive_event_bucket_1'] ?? '';

		// Ensure loader is registered so we can localize it
		add_eventive_dynamic_scripts();

		if ( wp_script_is( 'eventive-loader', 'registered' ) || wp_script_is( 'eventive-loader', 'enqueued' ) ) {
			wp_localize_script(
				'eventive-loader',
				'EventiveBlockData',
				array(
					'apiKey'      => $secret_key,
					'eventBucket' => $event_bucket,
				)
			);
		}
	}
);


// ====================================================
// META BOX: PER-PAGE EVENTIVE LOADER OVERRIDE
// ====================================================
// Enqueue admin styles for meta boxes
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) {
			wp_enqueue_style(
				'eventive-admin-style',
				plugin_dir_url( __FILE__ ) . 'admin/css/eventive-admin.css',
				array(),
				'1.0.0'
			);
		}
	}
);

// Add meta box for Eventive loader override
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'eventive_loader_override',
			'Event Bucket',
			'render_eventive_loader_override_box',
			array( 'page', 'post' ),
			'side'
		);
	}
);

// Render the meta box dropdown
function render_eventive_loader_override_box( $post ) {
	$selected_bucket = get_post_meta( $post->ID, '_eventive_loader_override', true );
	if ( ! function_exists( 'get_eventive_buckets' ) ) {
		echo '<p>Eventive buckets not available.</p>';
		return;
	}
	$buckets = get_eventive_buckets(); // Assume this function exists and returns id => name array

	echo '<label for="eventive_loader_override">Select Event Bucket:</label>';
	echo '<select name="eventive_loader_override" id="eventive_loader_override">';
	echo '<option value="">Default Loader</option>';
	foreach ( $buckets as $id => $name ) {
		$selected = selected( $selected_bucket, $id, false );
		echo "<option value='{$id}' {$selected}>{$name}</option>";
	}
	echo '</select>';
}

// Save the selected override on post save
add_action(
	'save_post',
	function ( $post_id ) {
		if ( array_key_exists( 'eventive_loader_override', $_POST ) ) {
			update_post_meta( $post_id, '_eventive_loader_override', sanitize_text_field( $_POST['eventive_loader_override'] ) );
		}
	}
);

/**
 * Retrieve Eventive event buckets as an associative array of id => name.
 *
 * @return array
 */
function get_eventive_buckets() {
	$options    = get_option( 'eventive_admin_options_option_name' );
	$secret_key = $options['your_eventive_secret_key_2'] ?? '';
	if ( empty( $secret_key ) ) {
		return array();
	}

	$cache_key = 'eventive_buckets_' . md5( $secret_key );
	$data      = get_transient( $cache_key );

	if ( false === $data ) {
		$response = wp_remote_get(
			'https://api.eventive.org/event_buckets',
			array(
				'headers' => array( 'x-api-key' => $secret_key ),
				'timeout' => 15,
			)
		);
		if ( is_wp_error( $response ) ) {
			return array();
		}
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( json_last_error() !== JSON_ERROR_NONE || empty( $data['event_buckets'] ) ) {
			return array();
		}
		set_transient( $cache_key, $data, 10 * MINUTE_IN_SECONDS );
	}

	$buckets = array();
	foreach ( $data['event_buckets'] as $bucket ) {
		if ( isset( $bucket['id'], $bucket['name'] ) ) {
			$buckets[ $bucket['id'] ] = $bucket['name'];
		}
	}

	return $buckets;
}

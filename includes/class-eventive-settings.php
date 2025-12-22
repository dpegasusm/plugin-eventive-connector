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
class Eventive_Settings {

	/**
	 * Init callback for register.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		// Register the Admin menu item for the settings page.
		add_action( 'admin_menu', array( $this, 'eventive_admin_menu' ) );

		// Register the actual settings.
		add_action( 'admin_init', array( $this, 'eventive_register_settings' ) );

		// Register AJAX handler for syncing events
		add_action( 'wp_ajax_sync_eventive_events', array( $this, 'sync_eventive_events_with_wordpress' ) );

		// Enqueue scripts for the Eventive options page.
		add_action( 'admin_enqueue_scripts', array( $this, 'eventive_enqueue_admin_scripts' ) );
	}

	/**
	 * Add administration menus.
	 *
	 * @return void
	 */
	public function eventive_admin_menu() {
		$page = add_options_page( __( 'EventiveWP', 'eventive' ), __( 'EventiveWP', 'eventive' ), 'manage_options', 'eventive_options', array( $this, 'eventive_options_page' ) );
	}

	/**
	 * Enqueue admin scripts only on the Eventive options page.
	 *
	 * @param string $hook The current admin page hook.
	 * @return void
	 */
	public function eventive_enqueue_admin_scripts( $hook ) {
		// Only load on the Eventive options page.
		if ( 'settings_page_eventive_options' !== $hook ) {
			return;
		}

		// Enqueue your custom script here.
		wp_enqueue_script(
			'eventive-options-script',
			EVENTIVE_PLUGIN . 'assets/js/eventive-options.js',
			array( 'jquery' ),
			EVENTIVE_CURRENT_VERSION,
			true
		);
	}

	/**
	 * Add our settings
	 *
	 * @return void
	 */
	public function eventive_register_settings() {
		// Create the Navbar section.
		add_settings_section( 'eventive_info_section', __( 'Eventive Configuration Settings', 'eventive' ), array( $this, 'eventive_admin_options_section_info' ), 'eventive_options' );

		// Add the Navbar settings.
		register_setting( 'eventive_options', 'eventive_navbar_box_title', 'sanitize_text_field' );

		// Fields to be added to the Navbar section.
		add_settings_field(
			'eventive_secret_key',
			esc_html__( 'Event Secret Key', 'eventive' ),
			array( $this, 'eventive_text_field_callback' ),
			'eventive_options',
			'eventive_info_section',
			array(
				'label_for' => 'eventive_secret_key',
				'label'     => esc_html__( 'Title to go in the callout box on the bottom of the nav menu.', 'eventive' ),
				'default'   => '',
			)
		);

		// Get the value of the secret key and if its set then add the event bucket id field.
		$secret_key = get_option( 'eventive_secret_key', '' );

		// Check for the secret key before adding the event bucket id field.
		if ( ! empty( $secret_key ) ) {
			// Add the Event Bucket ID field.
			register_setting( 'eventive_options', 'eventive_navbar_box_description', 'sanitize_text_field' );

			// add the settings field.
			add_settings_field(
				'eventive_event_bucket_id',
				esc_html__( 'Event Bucket ID', 'eventive' ),
				array( $this, 'eventive_dropdown_callback' ),
				'eventive_options',
				'eventive_info_section',
				array(
					'label_for' => 'eventive_event_bucket_id',
					'label'     => esc_html__( 'Text to go in the callout box on the bottom of the nav menu.', 'eventive' ),
					'default'   => '',
					'values'    => array(), // This will be populated via JS on the front. 
				)
			);
		}
	}

	/**
	 * Generate an option page.
	 *
	 * @return void
	 */
	public function eventive_options_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Eventive Settings', 'eventive' ); ?></h1>
			<form method="post" action="options.php" accept-charset="utf-8">
				<?php
				settings_fields( 'eventive_options' );

				do_settings_sections( 'eventive_options' );

				submit_button();
				?>
			</form>

			<h2><?php esc_html_e( 'Sync with Eventive', 'eventive' ); ?></h2>
			<p><?php esc_html_e( 'Click the buttons below to sync the events with Eventive.', 'eventive' ); ?></p>

			<!-- Eventive Events Button -->
			<form method="post" action="">
				<?php wp_nonce_field( 'eventive_sync_events', 'eventive_sync_events_nonce' ); ?>
				<button type="submit" name="eventive_sync_events" class="button button-primary">
					<?php esc_html_e( 'Sync with Eventive', 'eventive' ); ?>
				</button>
				<br>
				<div class='eventive-sync-progress' id='eventive-sync-events-progress' style='margin-top:10px; display:none;'>
					<?php esc_html_e( 'Syncing events, please wait...', 'eventive' ); ?>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Section info callback.
	 * 
	 * @return void
	 * 
	 */
	public function eventive_admin_options_section_info() {
		echo esc_html__( 'Welcome organizers! Use this page to configure the Eventive plugin options below.', 'eventive' );
	}

	/**
	 * Sanitize a yes/no checkbox/option.
	 *
	 * @param  string $input Yes no value to sanitize.
	 * @return string
	 */
	public function eventive_checkbox_sanitize( $input ) {
		if ( strtolower( $input ) === 'yes' ) {
			return 'yes';
		}
		return 'no';
	}

	/**
	 * A generic callback to display admin checkbox fields
	 *
	 * @param  mixed $args The args for the comment area.
	 * @return void
	 */
	public function eventive_checkbox_field_callback( $args ) {
		// We sanitize $args.
		$field   = esc_attr( $args['label_for'] );
		$label   = esc_html( $args['label'] );
		$default = esc_attr( $args['default'] );

		$value = $this->eventive_checkbox_sanitize( get_option( $field, $default ) );

		echo '<label class="description"><input type="checkbox" name="' . esc_attr( $field ) . '" id="' . esc_attr( $field ) . '" value="yes" ' . checked( $value, 'yes', false ) . '> ' . esc_attr( $label ) . '</label>';
	}

	/**
	 * A generic callback to display admin textfields.
	 *
	 * @param  array $args Args for callback.
	 * @return void
	 */
	public function eventive_text_field_callback( $args ) {
		// We sanitize $args.
		$field   = esc_attr( $args['label_for'] );
		$default = esc_attr( $args['default'] );

		$value = get_option( $field, $default );

		echo '<input type="text" name="' . esc_attr( $field ) . '" id="' . esc_attr( $field ) . '" value="' . esc_attr( $value ) . '" style="width: 100%;">';
	}

	/**
	 * A generic callback to display admin textfields.
	 *
	 * @param  array $args Args for callback.
	 * @return void
	 */
	public function eventive_upload_field_callback( $args ) {
		// We sanitize $args.
		$field   = esc_attr( $args['label_for'] );
		$default = esc_attr( $args['default'] );

		$value = get_option( $field, $default );

		echo '<input type="text" name="' . esc_attr( $field ) . '" id="' . esc_attr( $field ) . '-field" value="' . esc_attr( $value ) . '"> <button type="button" class="button button-secondary upload-button" id="' . esc_attr( $field ) . '-button" name="' . esc_attr( $field ) . '">' . esc_html__( 'Choose File', 'eventive' ) . '</button>';
	}

	/**
	 * A callback for URL Fields.
	 *
	 * @param  array $args Args for callback.
	 * @return void
	 */
	public function eventive_url_field_callback( $args ) {
		// We sanitize $args.
		$field   = esc_attr( $args['label_for'] );
		$default = esc_attr( $args['default'] );

		$value = get_option( $field, $default );

		echo '<input type="url" name="' . esc_attr( $field ) . '" id="' . esc_attr( $field ) . '" value="' . esc_attr( $value ) . '" style="width: 100%;">';
	}

	/**
	 * A generic callback to display admin textfields.
	 *
	 * @param  array $args Args for callback.
	 * @return void
	 */
	public function eventive_textarea_field_callback( $args ) {
		// We sanitize $args.
		$field   = esc_attr( $args['label_for'] );
		$default = esc_attr( $args['default'] );

		$value = get_option( $field, $default );

		echo '<textarea name="' . esc_attr( $field ) . '" id="' . esc_attr( $field ) . '" style="width: 100%;">' . esc_textarea( $value ) . '</textarea>';
	}

	/**
	 * Callback to display a dropdown of all pages on the site.
	 *
	 * @param array $args Arguments for the dropdown field.
	 * @return void
	 */
	public function eventive_dropdown_callback( $args ) {
		// Sanitize the arguments.
		$field   = esc_attr( $args['label_for'] );
		$default = esc_attr( $args['default'] );
		$values  = (array) ( isset( $args['values'] ) && is_array( $args['values'] ) ? $args['values'] : array() );

		// Get the current value of the setting.
		$value = get_option( $field, $default );

		// Start the dropdown.
		echo '<select name="' . esc_attr( $field ) . '" id="' . esc_attr( $field ) . '" style="width: 100%;">';
		echo '<option value="">' . esc_html__( 'Select an Option', 'eventive' ) . '</option>';

		// Loop through the pages and add them as options.
		foreach ( $values as $key => $label ) {
			$selected = selected( $value, $key, false );
			echo '<option value="' . esc_attr( $key ) . '" ' . esc_html( $selected ) . '>' . esc_html( $label ) . '</option>';
		}

		// Close the dropdown.
		echo '</select>';
	}

	/**
	 * Handle sync films with Eventive.
	 *
	 * @return void
	 */
	public function handle_sync_films_with_eventive() {
		// Handle Festival Films Sync.
		if ( isset( $_POST['eventive_sync_festival_films'] ) && check_admin_referer( 'eventive_sync_festival_films', 'eventive_sync_festival_films_nonce' ) ) {
			$this->sync_films_with_eventive();
		}
	}

	/**
	 * AJAX handler for syncing events with Eventive.
	 *
	 * @return void
	 */
	function sync_eventive_events_with_wordpress() {
		// Verify nonce.
		if ( ! isset( $_POST['eventive_sync_events_nonce'] ) || ! wp_verify_nonce( $_POST['eventive_sync_events_nonce'], 'eventive_sync_events' ) ) {
			wp_send_json_error( array( 'message' => 'Security verification failed.' ), 403 );
			return;
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'You do not have permission to perform this action.' ), 403 );
			return;
		}

		// Get API credentials.
		$options      = get_option( 'eventive_admin_options_option_name', array() );
		$event_bucket = $options['your_eventive_event_bucket_1'] ?? '';
		$api_key      = $options['your_eventive_secret_key_2'] ?? '';

		if ( empty( $event_bucket ) || empty( $api_key ) ) {
			wp_send_json_error( array( 'message' => 'Eventive API credentials are missing. Please configure them in the settings.' ), 400 );
			return;
		}

		// Fetch events from Eventive API.
		$url = "https://api.eventive.org/event_buckets/$event_bucket/events";
		
		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => "Bearer $api_key",
				),
				'timeout'   => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( array( 'message' => 'Failed to fetch events from Eventive: ' . $response->get_error_message() ), 500 );
			return;
		}

		$body   = wp_remote_retrieve_body( $response );
		$events = json_decode( $body, true );

		if ( empty( $events['events'] ) ) {
			wp_send_json_error( array( 'message' => 'No events found in the Eventive API response.' ), 404 );
			return;
		}

		$events       = $events['events'];
		$synced_count = 0;
		$updated_count = 0;
		$created_count = 0;

		foreach ( $events as $event ) {
			$event_id   = $event['id'] ?? null;
			$event_name = $event['name'] ?? 'Untitled Event';

			if ( empty( $event_id ) ) {
				continue;
			}

			$event_description = $event['description'] ?? '';
			$visibility        = $event['visibility'] ?? 'hidden';
			$post_status       = ( $visibility === 'published' ) ? 'publish' : 'draft';

			// Check if event already exists.
			$query = new WP_Query(
				array(
					'post_type'      => 'post',
					'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
					'posts_per_page' => 1,
					'meta_query'     => array(
						array(
							'key'     => '_eventive_event_id',
							'value'   => $event_id,
							'compare' => '=',
						),
					),
				)
			);

			$existing_post_id = ! empty( $query->posts ) ? $query->posts[0] : null;

			try {
				if ( $existing_post_id ) {
					// Update existing post.
					$post_data = array(
						'ID'           => $existing_post_id,
						'post_title'   => $event_name,
						'post_content' => $event_description,
						'post_status'  => $post_status,
						'post_type'    => 'post',
					);

					$post_id = wp_update_post( $post_data );

					if ( is_wp_error( $post_id ) ) {
						error_log( "Failed to update event: $event_name - " . $post_id->get_error_message() );
						continue;
					}

					$updated_count++;
				} else {
					// Create new post.
					$post_data = array(
						'post_title'   => $event_name,
						'post_content' => $event_description,
						'post_status'  => $post_status,
						'post_type'    => 'post',
					);

					$post_id = wp_insert_post( $post_data );

					if ( is_wp_error( $post_id ) ) {
						error_log( "Failed to create event: $event_name - " . $post_id->get_error_message() );
						continue;
					}

					$created_count++;
				}

				// Update post meta.
				update_post_meta( $post_id, '_eventive_event_id', $event_id );
				update_post_meta( $post_id, '_eventive_loader_override', $event_bucket );

				$synced_count++;

			} catch ( Exception $e ) {
				error_log( "Error syncing event $event_name: " . $e->getMessage() );
			}
		}

		$message = sprintf(
			'Successfully synced %d events (%d created, %d updated).',
			$synced_count,
			$created_count,
			$updated_count
		);

		wp_send_json_success(
			array(
				'message'       => $message,
				'synced_count'  => $synced_count,
				'created_count' => $created_count,
				'updated_count' => $updated_count,
			)
		);
	}
}




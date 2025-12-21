<?php
/**
 * Provincetown Film Plugin
 *
 * @package WordPress
 * @subpackage Provincetown
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dumplings_Member_List
 */
class Provincetown_Film_Settings {

	/**
	 * Init callback for register.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {
		// Register the Admin menu item for the settings page.
		add_action( 'admin_menu', array( $this, 'provincetown_admin_menu' ) );

		// Register the actual settings.
		add_action( 'admin_init', array( $this, 'provincetown_register_settings' ) );

		// Handle the Sync Films with Agile button.
		add_action( 'admin_init', array( $this, 'handle_sync_films_with_agile' ) );
	}

	/**
	 * Add administration menus.
	 *
	 * @return void
	 */
	public function provincetown_admin_menu() {
		$page = add_options_page( __( 'Provincetown', 'provincetown' ), __( 'Provincetown', 'provincetown' ), 'manage_options', 'provincetown_options', array( $this, 'provincetown_options_page' ) );
	}

	/**
	 * Add our settings
	 *
	 * @return void
	 */
	public function provincetown_register_settings() {
		// Create the Navbar section.
		add_settings_section( 'provincetown_navigation_section', __( 'Navigation Options', 'provincetown' ), '__return_true', 'provincetown_options' );

		// Add the Navbar settings.
		register_setting( 'provincetown_options', 'provincetown_navbar_box_title', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_navbar_box_description', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_navbar_box_link_callout', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_navbar_box_link_url', 'esc_url_raw' );

		// Fields to be added to the Navbar section.
		add_settings_field(
			'provincetown_navbar_box_title',
			esc_html__( 'Bottom Box Title', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_navigation_section',
			array(
				'label_for' => 'provincetown_navbar_box_title',
				'label'     => esc_html__( 'Title to go in the callout box on the bottom of the nav menu.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_navbar_box_description',
			esc_html__( 'Bottom Box Text', 'provincetown' ),
			array( $this, 'provincetown_textarea_field_callback' ),
			'provincetown_options',
			'provincetown_navigation_section',
			array(
				'label_for' => 'provincetown_navbar_box_description',
				'label'     => esc_html__( 'Text to go in the callout box on the bottom of the nav menu.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_navbar_box_link_callout',
			esc_html__( 'Bottom Box Button Text', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_navigation_section',
			array(
				'label_for' => 'provincetown_navbar_box_link_callout',
				'label'     => esc_html__( 'Link text to go in the callout box on the bottom of the nav menu.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_navbar_box_link_url',
			esc_html__( 'Bottom Box Callout URL', 'provincetown' ),
			array( $this, 'provincetown_url_field_callback' ),
			'provincetown_options',
			'provincetown_navigation_section',
			array(
				'label_for' => 'provincetown_navbar_box_link_url',
				'label'     => esc_html__( 'URL link for the callout box button on the bottom of the nav menu.', 'provincetown' ),
				'default'   => '',
			)
		);

		// Create the festival dates section.
		add_settings_section( 'provincetown_agile_links_section', __( 'Agile Links', 'provincetown' ), '__return_true', 'provincetown_options' );

		// Add the Navbar settings.
		register_setting( 'provincetown_options', 'provincetown_festival_agile_url', 'esc_url_raw' );
		register_setting( 'provincetown_options', 'provincetown_cinema_agile_url', 'esc_url_raw' );
		register_setting( 'provincetown_options', 'provincetown_special_agile_url', 'esc_url_raw' );

		add_settings_field(
			'provincetown_festival_agile_url',
			esc_html__( 'Festival Agile URL', 'provincetown' ),
			array( $this, 'provincetown_url_field_callback' ),
			'provincetown_options',
			'provincetown_agile_links_section',
			array(
				'label_for' => 'provincetown_festival_agile_url',
				'label'     => esc_html__( 'The URL of the API endpoint to call for the Festival films.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_cinema_agile_url',
			esc_html__( 'Cinema Agile URL', 'provincetown' ),
			array( $this, 'provincetown_url_field_callback' ),
			'provincetown_options',
			'provincetown_agile_links_section',
			array(
				'label_for' => 'provincetown_cinema_agile_url',
				'label'     => esc_html__( 'The URL of the API endpoint to call for the Cinema films.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_special_agile_url',
			esc_html__( 'Special Events Agile URL', 'provincetown' ),
			array( $this, 'provincetown_url_field_callback' ),
			'provincetown_options',
			'provincetown_agile_links_section',
			array(
				'label_for' => 'provincetown_special_agile_url',
				'label'     => esc_html__( 'The URL of the API endpoint to call for the Special Events films.', 'provincetown' ),
				'default'   => '',
			)
		);

		// Create the festival dates section.
		add_settings_section( 'provincetown_agile_api_section', __( 'Agile API Settings', 'provincetown' ), '__return_true', 'provincetown_options' );

		// Add the Navbar settings.
		register_setting( 'provincetown_options', 'provincetown_agile_api_app_key', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_agile_api_user_key', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_agile_api_corp_org_id', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_agile_api_item_org_id', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_agile_api_buyer_type_id', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_agile_api_valid_buyer_type_ids', 'sanitize_text_field' );

		add_settings_field(
			'provincetown_agile_api_app_key',
			esc_html__( 'Agile API App Key', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_api_section',
			array(
				'label_for' => 'provincetown_agile_api_app_key',
				'label'     => esc_html__( 'The container key for the API endpoint to call for the Festival films.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_agile_api_user_key',
			esc_html__( 'Agile API User Key', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_api_section',
			array(
				'label_for' => 'provincetown_agile_api_user_key',
				'label'     => esc_html__( 'The container key for the API endpoint to call for the Cinema films.', 'provincetown' ),
				'default'   => '',
			)
		);

		// Add missing settings fields for Agile API Settings.
		add_settings_field(
			'provincetown_agile_api_user_key',
			esc_html__( 'Agile API User Key', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_api_section',
			array(
				'label_for' => 'provincetown_agile_api_user_key',
				'label'     => esc_html__( 'The user key for the Agile API.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_agile_api_corp_org_id',
			esc_html__( 'Agile API Corporate Org ID', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_api_section',
			array(
				'label_for' => 'provincetown_agile_api_corp_org_id',
				'label'     => esc_html__( 'The corporate organization ID for the Agile API.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_agile_api_item_org_id',
			esc_html__( 'Agile API Item Org ID', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_api_section',
			array(
				'label_for' => 'provincetown_agile_api_item_org_id',
				'label'     => esc_html__( 'The item organization ID for the Agile API.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_agile_api_buyer_type_id',
			esc_html__( 'Agile API Default Buyer Type ID', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_api_section',
			array(
				'label_for' => 'provincetown_agile_api_buyer_type_id',
				'label'     => esc_html__( 'The buyer type ID for the Agile API.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_agile_api_valid_buyer_type_ids',
			esc_html__( 'Agile API valid buyer types', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_api_section',
			array(
				'label_for' => 'provincetown_agile_api_valid_buyer_type_ids',
				'label'     => esc_html__( 'Comma separated list of "online sales" buyer types that a user can have to purchase tickets for use in the Agile API.', 'provincetown' ),
				'default'   => '',
			)
		);

		// Create the Cart/Checkout section.
		add_settings_section(
			'provincetown_cart_checkout_section',
			__( 'Cart/Checkout Settings', 'provincetown' ),
			'__return_true',
			'provincetown_options'
		);

		// Register the "Enable New API Cart/Account Process" checkbox setting.
		register_setting(
			'provincetown_options',
			'provincetown_enable_new_api_cart',
			array( $this, 'provincetown_checkbox_sanitize' )
		);

		// Add the checkbox field for the new API cart/account process.
		add_settings_field(
			'provincetown_enable_new_api_cart',
			__( 'Enable New API Cart/Account Process', 'provincetown' ),
			array( $this, 'provincetown_checkbox_field_callback' ),
			'provincetown_options',
			'provincetown_cart_checkout_section',
			array(
				'label_for' => 'provincetown_enable_new_api_cart',
				'label'     => __( 'Enable the new API-based cart and account process.', 'provincetown' ),
				'default'   => 'no',
			)
		);

		// Register the "Use Cart/Checkout page on site" checkbox setting.
		register_setting(
			'provincetown_options',
			'provincetown_use_cart_checkout',
			array( $this, 'provincetown_checkbox_sanitize' )
		);

		// Add the checkbox field.
		add_settings_field(
			'provincetown_use_cart_checkout',
			__( 'Use Cart/Checkout Page', 'provincetown' ),
			array( $this, 'provincetown_checkbox_field_callback' ),
			'provincetown_options',
			'provincetown_cart_checkout_section',
			array(
				'label_for' => 'provincetown_use_cart_checkout',
				'label'     => __( 'Enable this option to use a specific page on the site for the cart/checkout.', 'provincetown' ),
				'default'   => 'no',
			)
		);

		// Register the dropdown setting for selecting the cart/checkout page.
		register_setting(
			'provincetown_options',
			'provincetown_cart_checkout_page',
			'sanitize_text_field'
		);

		// Add the dropdown field.
		add_settings_field(
			'provincetown_cart_checkout_page',
			__( 'Cart/Checkout Page', 'provincetown' ),
			array( $this, 'provincetown_page_dropdown_callback' ),
			'provincetown_options',
			'provincetown_cart_checkout_section',
			array(
				'label_for' => 'provincetown_cart_checkout_page',
				'label'     => __( 'Select the page to use for the cart/checkout.', 'provincetown' ),
				'default'   => '',
			)
		);

		// Create the festival dates section.
		add_settings_section( 'provincetown_agile_containers_section', __( 'Agile Containers', 'provincetown' ), '__return_true', 'provincetown_options' );

		// Add the Navbar settings.
		register_setting( 'provincetown_options', 'provincetown_festival_agile_container', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_cinema_agile_container', 'sanitize_text_field' );
		register_setting( 'provincetown_options', 'provincetown_special_agile_container', 'sanitize_text_field' );

		add_settings_field(
			'provincetown_festival_agile_container',
			esc_html__( 'Festival Agile Container Key', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_containers_section',
			array(
				'label_for' => 'provincetown_festival_agile_container',
				'label'     => esc_html__( 'The container key for the API endpoint to call for the Festival films.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_cinema_agile_container',
			esc_html__( 'Cinema Agile Container Key', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_containers_section',
			array(
				'label_for' => 'provincetown_cinema_agile_container',
				'label'     => esc_html__( 'The container key for the API endpoint to call for the Cinema films.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_special_agile_container',
			esc_html__( 'Special Events Agile Container Key', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_agile_containers_section',
			array(
				'label_for' => 'provincetown_special_agile_container',
				'label'     => esc_html__( 'The container key for the API endpoint to call for the Special Events films.', 'provincetown' ),
				'default'   => '',
			)
		);

		add_settings_section( 'provincetown_festival_section', __( 'Festival Settings', 'provincetown' ), '__return_true', 'provincetown_options' );

		// register the setting.
		register_setting( 'provincetown_options', 'provincetown_festival_toggle', array( $this, 'provincetown_checkbox_sanitize' ) );
		register_setting( 'provincetown_options', 'provincetown_festival_closed_text', 'sanitize_text_field' );

		// filds themselves.
		add_settings_field(
			'provincetown_festival_toggle',
			__( 'Ticket Sales Closed', 'provincetown' ),
			array( $this, 'provincetown_checkbox_field_callback' ),
			'provincetown_options',
			'provincetown_festival_section',
			array(
				'label_for' => 'provincetown_festival_toggle',
				'label'     => 'Check the box to close ticket sales and display the message below.',
				'default'   => '',
			)
		);

		add_settings_field(
			'provincetown_festival_closed_text',
			__( 'Closed Text', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_festival_section',
			array(
				'label_for' => 'provincetown_festival_closed_text',
				'label'     => 'Text to display on the purchase tickets screen when sales are closed.',
				'default'   => '',
			)
		);

		// Create the festival dates section.
		add_settings_section( 'provincetown_festival_dates_section', __( 'Festival Dates', 'provincetown' ), '__return_true', 'provincetown_options' );

		// Add the Navbar settings.
		register_setting( 'provincetown_options', 'provincetown_festival_header_dates', 'sanitize_text_field' );

		add_settings_field(
			'provincetown_festival_header_dates',
			esc_html__( 'Festival Dates', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_festival_dates_section',
			array(
				'label_for' => 'provincetown_festival_header_dates',
				'label'     => esc_html__( 'Dates to display at the top of the festival branded pages.', 'provincetown' ),
				'default'   => '',
			)
		);

		// Create the section.
		add_settings_section( 'provincetown_program_film_pages_section', __( 'Film Page Options', 'provincetown' ), '__return_true', 'provincetown_options' );

		// Add the Navbar settings.
		register_setting( 'provincetown_options', 'provincetown_festival_no_showtimes_found', 'sanitize_text_field' );

		add_settings_field(
			'provincetown_festival_no_showtimes_found',
			esc_html__( 'No Showtimes found Text', 'provincetown' ),
			array( $this, 'provincetown_text_field_callback' ),
			'provincetown_options',
			'provincetown_program_film_pages_section',
			array(
				'label_for' => 'provincetown_festival_no_showtimes_found',
				'label'     => esc_html__( 'Text to show on the film pages when no showtimes are found.', 'provincetown' ),
				'default'   => __( "We're sorry, showtimes for this film are currently unavailable.", 'provincetown' ),
			)
		);

		// fields to it.
		add_settings_section( 'provincetown_users_section', __( 'Users', 'provincetown' ), '__return_true', 'provincetown_options' );

		// register the setting.
		register_setting( 'provincetown_options', 'provincetown_create_users', array( $this, 'provincetown_checkbox_sanitize' ) );

		// Add the field with the names and function to use for our new.
		// settings, put it in our new section.
		add_settings_field(
			'provincetown_create_users',
			__( 'Auto create user accounts', 'provincetown' ),
			array( $this, 'provincetown_checkbox_field_callback' ),
			'provincetown_options',
			'provincetown_users_section',
			array(
				'label_for' => 'provincetown_create_users',
				'label'     => __( 'Automatically create a WordPress user account when an Agile user sign-in for the first time.', 'provincetown' ),
			)
		);

		add_settings_section( 'provincetown_password_section', __( 'Passwords', 'provincetown' ), '__return_true', 'provincetown_options' );

		register_setting( 'provincetown_options', 'provincetown_show_password_field', array( $this, 'provincetown_checkbox_sanitize' ) );

		add_settings_field(
			'provincetown_show_password_field',
			__( 'Show password fields', 'provincetown' ),
			array( $this, 'provincetown_checkbox_field_callback' ),
			'provincetown_options',
			'provincetown_password_section',
			array(
				'label_for' => 'provincetown_show_password_field',
				'label'     => __( 'Display the password field for Admins (hidden for everyone else).', 'provincetown' ),
			)
		);
	}

	/**
	 * Generate an option page.
	 *
	 * @return void
	 */
	public function provincetown_options_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Provincetown Settings', 'provincetown' ); ?></h1>

			<h2><?php esc_html_e( 'Sync Films with Agile', 'provincetown' ); ?></h2>
			<p><?php esc_html_e( 'Click the buttons below to sync the respective films with Agile.', 'provincetown' ); ?></p>

			<!-- Festival Films Button -->
			<form method="post" action="">
				<?php wp_nonce_field( 'provincetown_sync_festival_films', 'provincetown_sync_festival_films_nonce' ); ?>
				<button type="submit" name="provincetown_sync_festival_films" class="button button-primary">
					<?php esc_html_e( 'Sync Festival Films with Agile', 'provincetown' ); ?>
				</button>
				<br>
				<br>
			</form>

			<!-- Cinema Films Button -->
			<form method="post" action="">
				<?php wp_nonce_field( 'provincetown_sync_cinema_films', 'provincetown_sync_cinema_films_nonce' ); ?>
				<button type="submit" name="provincetown_sync_cinema_films" class="button button-primary">
					<?php esc_html_e( 'Sync Cinema Films with Agile', 'provincetown' ); ?>
				</button>
				<br>
				<br>
			</form>

			<!-- Event Films Button -->
			<form method="post" action="">
				<?php wp_nonce_field( 'provincetown_sync_event_films', 'provincetown_sync_event_films_nonce' ); ?>
				<button type="submit" name="provincetown_sync_event_films" class="button button-primary">
					<?php esc_html_e( 'Sync Event Films with Agile', 'provincetown' ); ?>
				</button>
				<br>
			</form>

			<form method="post" action="options.php" accept-charset="utf-8">
		<?php
		settings_fields( 'provincetown_options' );

		do_settings_sections( 'provincetown_options' );

		submit_button();
		?>
			</form>
		</div>
		<?php
	}

	/**
	 * Sanitize a yes/no checkbox/option.
	 *
	 * @param  string $input Yes no value to sanitize.
	 * @return string
	 */
	public function provincetown_checkbox_sanitize( $input ) {
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
	public function provincetown_checkbox_field_callback( $args ) {
		// We sanitize $args.
		$field   = esc_attr( $args['label_for'] );
		$label   = esc_html( $args['label'] );
		$default = esc_attr( $args['default'] );

		$value = $this->provincetown_checkbox_sanitize( get_option( $field, $default ) );

		echo '<label class="description"><input type="checkbox" name="' . esc_attr( $field ) . '" id="' . esc_attr( $field ) . '" value="yes" ' . checked( $value, 'yes', false ) . '> ' . esc_attr( $label ) . '</label>';
	}

	/**
	 * A generic callback to display admin textfields.
	 *
	 * @param  array $args Args for callback.
	 * @return void
	 */
	public function provincetown_text_field_callback( $args ) {
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
	public function provincetown_upload_field_callback( $args ) {
		// We sanitize $args.
		$field   = esc_attr( $args['label_for'] );
		$default = esc_attr( $args['default'] );

		$value = get_option( $field, $default );

		echo '<input type="text" name="' . esc_attr( $field ) . '" id="' . esc_attr( $field ) . '-field" value="' . esc_attr( $value ) . '"> <button type="button" class="button button-secondary upload-button" id="' . esc_attr( $field ) . '-button" name="' . esc_attr( $field ) . '">' . esc_html__( 'Choose File', 'provincetown' ) . '</button>';
	}

	/**
	 * A callback for URL Fields.
	 *
	 * @param  array $args Args for callback.
	 * @return void
	 */
	public function provincetown_url_field_callback( $args ) {
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
	public function provincetown_textarea_field_callback( $args ) {
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
	public function provincetown_page_dropdown_callback( $args ) {
		// Sanitize the arguments.
		$field   = esc_attr( $args['label_for'] );
		$default = esc_attr( $args['default'] );

		// Get the current value of the setting.
		$value = get_option( $field, $default );

		// Get all published pages.
		$pages = get_pages();

		// Start the dropdown.
		echo '<select name="' . esc_attr( $field ) . '" id="' . esc_attr( $field ) . '" style="width: 100%;">';
		echo '<option value="">' . esc_html__( 'Select a page', 'provincetown' ) . '</option>';

		// Loop through the pages and add them as options.
		foreach ( $pages as $page ) {
			$selected = selected( $value, $page->ID, false );
			echo '<option value="' . esc_attr( $page->ID ) . '" ' . esc_html( $selected ) . '>' . esc_html( $page->post_title ) . '</option>';
		}

		// Close the dropdown.
		echo '</select>';
	}

	/**
	 * Validators for saving a post
	 *
	 * @param  string $input Movie rating.
	 * @return string
	 */
	public function sanitize_movie_rating( $input ) {
		$valid = array( '', 'PG', 'PG-13', 'R', 'NC-17' );

		if ( in_array( $input, $valid, true ) ) {
			return $input;
		}

		return '';
	}

	/**
	 * Trailer source sanitize callback.
	 *
	 * @param  string $input Trailer video type.
	 * @return string
	 */
	public function sanitize_trailer_source( $input ) {
		$valid = array( '', 'youtube', 'vimeo', 'link' );

		if ( in_array( $input, $valid, true ) ) {
			return $input;
		}

		return '';
	}

	/**
	 * Sanitize the movie timing field.
	 *
	 * @param  string $input Where is this beng played.
	 * @return string
	 */
	public function sanitize_movie_timing( $input ) {
		$valid = array( 'Coming Soon', 'In Theater', 'Past' );

		if ( in_array( $input, $valid, true ) ) {
			return $input;
		}

		return '';
	}

	/**
	 * Sanitize the Yes/No Field.
	 *
	 * @param  string $input Yes no value checker.
	 * @return string
	 */
	public function provincetown_sanitize_film_yes_no( $input ) {
		$valid = array( 'yes', 'no' );

		if ( in_array( $input, $valid, true ) ) {
			return $input;
		}

		return 'no';
	}

	/**
	 * Handle sync films with Agile.
	 *
	 * @return void
	 */
	public function handle_sync_films_with_agile() {
		// Handle Festival Films Sync.
		if ( isset( $_POST['provincetown_sync_festival_films'] ) && check_admin_referer( 'provincetown_sync_festival_films', 'provincetown_sync_festival_films_nonce' ) ) {
			$this->sync_films_by_type( 'festival', 'Festival Films' );
		}

		// Handle Cinema Films Sync.
		if ( isset( $_POST['provincetown_sync_cinema_films'] ) && check_admin_referer( 'provincetown_sync_cinema_films', 'provincetown_sync_cinema_films_nonce' ) ) {
			$this->sync_films_by_type( 'cinema', 'Cinema Films' );
		}

		// Handle Event Films Sync.
		if ( isset( $_POST['provincetown_sync_event_films'] ) && check_admin_referer( 'provincetown_sync_event_films', 'provincetown_sync_event_films_nonce' ) ) {
			$this->sync_films_by_type( 'event', 'Event Films' );
		}
	}

	/**
	 * Sync films by type.
	 *
	 * @param string $type  The type of films to sync.
	 * @param string $label The label for the admin notice.
	 * @return void
	 */
	private function sync_films_by_type( $type, $label ) {
		global $class_provincetown_film;

		switch ( $type ) {
			case 'festival':
				$post_type = 'films';
				break;
			case 'cinema':
				$post_type = 'cinema_films';
				break;
			case 'event':
				$post_type = 'event_films';
				break;
			default:
				return;
		}

		// Get all published films of the specified type.
		$films = get_posts(
			array(
				'post_type'   => $post_type,
				'post_status' => 'publish',
				'numberposts' => -1,
			)
		);

		if ( ! empty( $films ) ) {
			foreach ( $films as $film ) {
				// Call the provincetown_update_agile_film_data function for each film.
				$class_provincetown_film->provincetown_update_agile_film_data( null, $film->ID, 'provincetown_film_agile_id', null );
			}

			// Add an admin notice to confirm the sync.
			add_action(
				'admin_notices',
				function () use ( $label ) {
					// Translators: %s is the label of the films synced.
					echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( '%s successfully synced with Agile.', 'provincetown' ), esc_html( $label ) ) . '</p></div>';
				}
			);
		} else {
			// Add an admin notice if no films were found.
			add_action(
				'admin_notices',
				function () use ( $label ) {
					// Translators: %s is the label of the films synced.
					echo '<div class="notice notice-warning is-dismissible"><p>' . sprintf( esc_html__( 'No %s found to sync.', 'provincetown' ), esc_html( $label ) ) . '</p></div>';
				}
			);
		}
	}
}

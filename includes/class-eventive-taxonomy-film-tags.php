<?php
/**
 * Eventive Plugin - Film Tags Taxonomy
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
 * Eventive_Taxonomy_Film_Tags Class
 */
class Eventive_Taxonomy_Film_Tags {
	/**
	 * Initialize the custom taxonomy.
	 *
	 * @return void
	 */
	public function init() {
		// Register the Eventive film tags taxonomy.
		add_action( 'init', array( $this, 'register_eventive_taxonomy_tags' ) );
	}

	/**
	 * Register the Eventive film tags taxonomy.
	 *
	 * @return void
	 */
	public function register_eventive_taxonomy_tags() {
		$labels = array(
			'name'                       => _x( 'Film Tags', 'taxonomy general name', 'eventive' ),
			'singular_name'              => _x( 'Film Tag', 'taxonomy singular name', 'eventive' ),
			'search_items'               => __( 'Search Film Tags', 'eventive' ),
			'popular_items'              => __( 'Popular Film Tags', 'eventive' ),
			'all_items'                  => __( 'All Film Tags', 'eventive' ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Film Tag', 'eventive' ),
			'update_item'                => __( 'Update Film Tag', 'eventive' ),
			'add_new_item'               => __( 'Add New Film Tag', 'eventive' ),
			'new_item_name'              => __( 'New Film Tag Name', 'eventive' ),
			'separate_items_with_commas' => __( 'Separate film tags with commas', 'eventive' ),
			'add_or_remove_items'        => __( 'Add or remove film tags', 'eventive' ),
			'choose_from_most_used'      => __( 'Choose from the most used film tags', 'eventive' ),
			'not_found'                  => __( 'No film tags found.', 'eventive' ),
			'menu_name'                  => __( 'Film Tags', 'eventive' ),
		);

		$args = array(
			'labels'            => $labels,
			'description'       => __( 'Tags for films imported from Eventive.', 'eventive' ),
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'show_tagcloud'     => true,
			'show_in_quick_edit' => true,
			'show_admin_column' => true,
			'rewrite'           => array(
				'slug'         => 'film-tag',
				'with_front'   => false,
				'hierarchical' => false,
			),
			'query_var'         => true,
			'capabilities'      => array(
				'manage_terms' => 'manage_categories',
				'edit_terms'   => 'manage_categories',
				'delete_terms' => 'manage_categories',
				'assign_terms' => 'edit_posts',
			),
		);

		register_taxonomy( 'eventive_film_tags', array( 'eventive_film' ), $args );
	}
}

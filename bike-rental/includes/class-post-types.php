<?php
/**
 * Custom post type and taxonomy registration.
 *
 * @package BikeRental
 */

namespace BikeRental;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the `bike`, `booking`, and `bike_type` data structures.
 */
class Post_Types {

	/**
	 * Post type key for bicycles.
	 *
	 * @var string
	 */
	const BIKE_CPT = 'bike';

	/**
	 * Post type key for bookings.
	 *
	 * @var string
	 */
	const BOOKING_CPT = 'booking';

	/**
	 * Taxonomy key for bike categories (road, mountain, electric, ...).
	 *
	 * @var string
	 */
	const BIKE_TYPE_TAX = 'bike_type';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Register the custom post types.
	 *
	 * Public so it can be reused by the activation hook.
	 *
	 * @return void
	 */
	public function register_post_types() {
		$this->register_bike();
		$this->register_booking();
	}

	/**
	 * Register the `bike` post type.
	 *
	 * @return void
	 */
	private function register_bike() {
		$labels = array(
			'name'                  => _x( 'Bikes', 'Post type general name', 'bike-rental' ),
			'singular_name'         => _x( 'Bike', 'Post type singular name', 'bike-rental' ),
			'menu_name'             => _x( 'Bikes', 'Admin menu text', 'bike-rental' ),
			'name_admin_bar'        => _x( 'Bike', 'Add new on toolbar', 'bike-rental' ),
			'add_new'               => __( 'Add New', 'bike-rental' ),
			'add_new_item'          => __( 'Add New Bike', 'bike-rental' ),
			'new_item'              => __( 'New Bike', 'bike-rental' ),
			'edit_item'             => __( 'Edit Bike', 'bike-rental' ),
			'view_item'             => __( 'View Bike', 'bike-rental' ),
			'all_items'             => __( 'All Bikes', 'bike-rental' ),
			'search_items'          => __( 'Search Bikes', 'bike-rental' ),
			'not_found'             => __( 'No bikes found.', 'bike-rental' ),
			'not_found_in_trash'    => __( 'No bikes found in Trash.', 'bike-rental' ),
			'featured_image'        => __( 'Bike Photo', 'bike-rental' ),
			'set_featured_image'    => __( 'Set bike photo', 'bike-rental' ),
			'remove_featured_image' => __( 'Remove bike photo', 'bike-rental' ),
			'archives'              => __( 'Bike Archives', 'bike-rental' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Bicycles available for rent.', 'bike-rental' ),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_in_admin_bar'  => true,
			'show_in_rest'       => true,
			'query_var'          => true,
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 26,
			'menu_icon'          => 'dashicons-buddicons-activity',
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
			'rewrite'            => array(
				'slug'       => 'bikes',
				'with_front' => false,
			),
			'capability_type'    => 'post',
			'map_meta_cap'       => true,
		);

		register_post_type( self::BIKE_CPT, $args );
	}

	/**
	 * Register the `booking` post type.
	 *
	 * Bookings are not publicly queryable; they are managed from the admin and
	 * referenced by the front-end form handler.
	 *
	 * @return void
	 */
	private function register_booking() {
		$labels = array(
			'name'               => _x( 'Bookings', 'Post type general name', 'bike-rental' ),
			'singular_name'      => _x( 'Booking', 'Post type singular name', 'bike-rental' ),
			'menu_name'          => _x( 'Bookings', 'Admin menu text', 'bike-rental' ),
			'add_new'            => __( 'Add New', 'bike-rental' ),
			'add_new_item'       => __( 'Add New Booking', 'bike-rental' ),
			'new_item'           => __( 'New Booking', 'bike-rental' ),
			'edit_item'          => __( 'Edit Booking', 'bike-rental' ),
			'view_item'          => __( 'View Booking', 'bike-rental' ),
			'all_items'          => __( 'All Bookings', 'bike-rental' ),
			'search_items'       => __( 'Search Bookings', 'bike-rental' ),
			'not_found'          => __( 'No bookings found.', 'bike-rental' ),
			'not_found_in_trash' => __( 'No bookings found in Trash.', 'bike-rental' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Rental bookings for bicycles.', 'bike-rental' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'show_in_rest'        => false,
			'query_var'           => false,
			'has_archive'         => false,
			'hierarchical'        => false,
			'menu_position'       => 27,
			'menu_icon'           => 'dashicons-calendar-alt',
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
		);

		register_post_type( self::BOOKING_CPT, $args );
	}

	/**
	 * Register the bike type taxonomy.
	 *
	 * @return void
	 */
	public function register_taxonomies() {
		$labels = array(
			'name'              => _x( 'Bike Types', 'taxonomy general name', 'bike-rental' ),
			'singular_name'     => _x( 'Bike Type', 'taxonomy singular name', 'bike-rental' ),
			'search_items'      => __( 'Search Bike Types', 'bike-rental' ),
			'all_items'         => __( 'All Bike Types', 'bike-rental' ),
			'edit_item'         => __( 'Edit Bike Type', 'bike-rental' ),
			'update_item'       => __( 'Update Bike Type', 'bike-rental' ),
			'add_new_item'      => __( 'Add New Bike Type', 'bike-rental' ),
			'new_item_name'     => __( 'New Bike Type Name', 'bike-rental' ),
			'menu_name'         => __( 'Bike Types', 'bike-rental' ),
		);

		register_taxonomy(
			self::BIKE_TYPE_TAX,
			array( self::BIKE_CPT ),
			array(
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'bike-type' ),
			)
		);
	}
}

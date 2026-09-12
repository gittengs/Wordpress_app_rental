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
			'name'                  => _x( 'Sykler', 'Post type general name', 'bike-rental' ),
			'singular_name'         => _x( 'Sykkel', 'Post type singular name', 'bike-rental' ),
			'menu_name'             => _x( 'Sykler', 'Admin menu text', 'bike-rental' ),
			'name_admin_bar'        => _x( 'Sykkel', 'Add new on toolbar', 'bike-rental' ),
			'add_new'               => __( 'Legg til ny', 'bike-rental' ),
			'add_new_item'          => __( 'Legg til ny sykkel', 'bike-rental' ),
			'new_item'              => __( 'Ny sykkel', 'bike-rental' ),
			'edit_item'             => __( 'Rediger sykkel', 'bike-rental' ),
			'view_item'             => __( 'Vis sykkel', 'bike-rental' ),
			'all_items'             => __( 'Alle sykler', 'bike-rental' ),
			'search_items'          => __( 'Søk i sykler', 'bike-rental' ),
			'not_found'             => __( 'Ingen sykler funnet.', 'bike-rental' ),
			'not_found_in_trash'    => __( 'Ingen sykler funnet i papirkurven.', 'bike-rental' ),
			'featured_image'        => __( 'Sykkelbilde', 'bike-rental' ),
			'set_featured_image'    => __( 'Velg sykkelbilde', 'bike-rental' ),
			'remove_featured_image' => __( 'Fjern sykkelbilde', 'bike-rental' ),
			'archives'              => __( 'Sykkelarkiv', 'bike-rental' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Sykler tilgjengelig for utleie.', 'bike-rental' ),
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
			'name'               => _x( 'Bookinger', 'Post type general name', 'bike-rental' ),
			'singular_name'      => _x( 'Booking', 'Post type singular name', 'bike-rental' ),
			'menu_name'          => _x( 'Bookinger', 'Admin menu text', 'bike-rental' ),
			'add_new'            => __( 'Legg til ny', 'bike-rental' ),
			'add_new_item'       => __( 'Legg til ny booking', 'bike-rental' ),
			'new_item'           => __( 'Ny booking', 'bike-rental' ),
			'edit_item'          => __( 'Rediger booking', 'bike-rental' ),
			'view_item'          => __( 'Vis booking', 'bike-rental' ),
			'all_items'          => __( 'Alle bookinger', 'bike-rental' ),
			'search_items'       => __( 'Søk i bookinger', 'bike-rental' ),
			'not_found'          => __( 'Ingen bookinger funnet.', 'bike-rental' ),
			'not_found_in_trash' => __( 'Ingen bookinger funnet i papirkurven.', 'bike-rental' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Utleiebookinger for sykler.', 'bike-rental' ),
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
			'name'              => _x( 'Sykkeltyper', 'taxonomy general name', 'bike-rental' ),
			'singular_name'     => _x( 'Sykkeltype', 'taxonomy singular name', 'bike-rental' ),
			'search_items'      => __( 'Søk i sykkeltyper', 'bike-rental' ),
			'all_items'         => __( 'Alle sykkeltyper', 'bike-rental' ),
			'edit_item'         => __( 'Rediger sykkeltype', 'bike-rental' ),
			'update_item'       => __( 'Oppdater sykkeltype', 'bike-rental' ),
			'add_new_item'      => __( 'Legg til ny sykkeltype', 'bike-rental' ),
			'new_item_name'     => __( 'Navn på ny sykkeltype', 'bike-rental' ),
			'menu_name'         => __( 'Sykkeltyper', 'bike-rental' ),
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

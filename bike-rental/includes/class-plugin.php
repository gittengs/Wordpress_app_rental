<?php
/**
 * Main plugin bootstrap.
 *
 * @package BikeRental
 */

namespace BikeRental;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the plugin's feature classes together and handles activation lifecycle.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Retrieve the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor — use {@see Plugin::instance()}.
	 */
	private function __construct() {}

	/**
	 * Register plugin hooks. Called on `plugins_loaded`.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		( new Post_Types() )->register_hooks();
		( new Meta() )->register_hooks();
		( new Bookings() )->register_hooks();
		( new Shortcodes() )->register_hooks();
		( new Assets() )->register_hooks();

		if ( is_admin() ) {
			( new Settings() )->register_hooks();
		}

		/**
		 * Fires after the Bike Rental plugin has been initialized.
		 *
		 * @param Plugin $plugin The plugin instance.
		 */
		do_action( 'bike_rental_init', $this );
	}

	/**
	 * Load the plugin text domain.
	 *
	 * Hooked on `init` so translations are loaded at the correct time
	 * (loading them earlier triggers a `_doing_it_wrong()` notice on WP 6.7+).
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'bike-rental',
			false,
			dirname( BIKE_RENTAL_BASENAME ) . '/languages'
		);
	}

	/**
	 * Activation callback.
	 *
	 * Registers the custom post types and flushes rewrite rules so that the
	 * bike permalinks resolve immediately after activation.
	 *
	 * @return void
	 */
	public static function activate() {
		$post_types = new Post_Types();
		$post_types->register_post_types();

		flush_rewrite_rules();
	}

	/**
	 * Deactivation callback.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}

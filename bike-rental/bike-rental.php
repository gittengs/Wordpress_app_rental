<?php
/**
 * Plugin Name:       Bike Rental
 * Plugin URI:        https://example.com/bike-rental
 * Description:       Add a complete bicycle rental system to WordPress: manage your fleet, track availability, and take bookings.
 * Version:           0.1.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Bike Rental Team
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bike-rental
 * Domain Path:       /languages
 *
 * @package BikeRental
 */

namespace BikeRental;

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Plugin constants.
 * ---------------------------------------------------------------------- */

define( 'BIKE_RENTAL_VERSION', '0.1.2' );
define( 'BIKE_RENTAL_FILE', __FILE__ );
define( 'BIKE_RENTAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'BIKE_RENTAL_URL', plugin_dir_url( __FILE__ ) );
define( 'BIKE_RENTAL_BASENAME', plugin_basename( __FILE__ ) );

/* -------------------------------------------------------------------------
 * Autoloader.
 *
 * Maps namespaced classes to WordPress-style file names. For example:
 *   BikeRental\Post_Types  ->  includes/class-post-types.php
 * ---------------------------------------------------------------------- */

spl_autoload_register(
	/**
	 * Autoload BikeRental classes.
	 *
	 * @param string $class Fully-qualified class name.
	 * @return void
	 */
	function ( $class ) {
		$prefix = __NAMESPACE__ . '\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$relative = strtolower( str_replace( '_', '-', $relative ) );
		$file     = BIKE_RENTAL_DIR . 'includes/class-' . $relative . '.php';

		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
);

/* -------------------------------------------------------------------------
 * Lifecycle hooks (must be registered from the main plugin file).
 * ---------------------------------------------------------------------- */

register_activation_hook( __FILE__, array( __NAMESPACE__ . '\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\\Plugin', 'deactivate' ) );

/**
 * Boot the plugin once all plugins are loaded.
 *
 * @return void
 */
function bike_rental() {
	static $instance = null;

	if ( null === $instance ) {
		$instance = Plugin::instance();
		$instance->init();
	}

	return $instance;
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\bike_rental' );

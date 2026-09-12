<?php
/**
 * Front-end asset registration and enqueueing.
 *
 * @package BikeRental
 */

namespace BikeRental;

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues the plugin's CSS and JavaScript.
 */
class Assets {

	/**
	 * Handle used for the stylesheet.
	 *
	 * @var string
	 */
	const STYLE_HANDLE = 'bike-rental';

	/**
	 * Handle used for the script.
	 *
	 * @var string
	 */
	const SCRIPT_HANDLE = 'bike-rental';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend' ) );
	}

	/**
	 * Enqueue front-end assets when relevant content is present.
	 *
	 * @return void
	 */
	public function enqueue_frontend() {
		wp_register_style(
			self::STYLE_HANDLE,
			BIKE_RENTAL_URL . 'assets/css/bike-rental.css',
			array(),
			BIKE_RENTAL_VERSION
		);

		wp_register_script(
			self::SCRIPT_HANDLE,
			BIKE_RENTAL_URL . 'assets/js/bike-rental.js',
			array(),
			BIKE_RENTAL_VERSION,
			true
		);

		if ( $this->should_enqueue() ) {
			wp_enqueue_style( self::STYLE_HANDLE );
			wp_enqueue_script( self::SCRIPT_HANDLE );
		}
	}

	/**
	 * Decide whether the current request needs the plugin assets.
	 *
	 * Assets load when the singular content contains one of our shortcodes, or on
	 * the bike post type archive / single views.
	 *
	 * @return bool
	 */
	private function should_enqueue() {
		if ( is_singular( Post_Types::BIKE_CPT ) || is_post_type_archive( Post_Types::BIKE_CPT ) ) {
			return true;
		}

		if ( is_singular() ) {
			$post = get_post();
			if ( $post instanceof \WP_Post ) {
				if ( has_shortcode( $post->post_content, 'bike_listing' )
					|| has_shortcode( $post->post_content, 'bike_booking_form' ) ) {
					return true;
				}
			}
		}

		return (bool) apply_filters( 'bike_rental_should_enqueue_assets', false );
	}
}

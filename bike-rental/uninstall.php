<?php
/**
 * Uninstall routine.
 *
 * Runs when the plugin is deleted from the Plugins screen. Removes the options
 * created by the plugin. Bike and booking posts are intentionally preserved so
 * that a site owner does not lose data by accident; set BIKE_RENTAL_REMOVE_DATA
 * to true (e.g. via a mu-plugin) to also delete them.
 *
 * @package BikeRental
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Delete the plugin's stored options.
 *
 * @return void
 */
function bike_rental_delete_options() {
	$options = array(
		'bike_rental_currency_symbol',
		'bike_rental_business_name',
		'bike_rental_booking_page',
		'bike_rental_terms_text',
	);

	foreach ( $options as $option ) {
		delete_option( $option );
	}
}

bike_rental_delete_options();

if ( defined( 'BIKE_RENTAL_REMOVE_DATA' ) && BIKE_RENTAL_REMOVE_DATA ) {
	$post_types = array( 'bike', 'booking' );

	foreach ( $post_types as $post_type ) {
		$posts = get_posts(
			array(
				'post_type'        => $post_type,
				'post_status'      => 'any',
				'numberposts'      => -1,
				'fields'           => 'ids',
				'suppress_filters' => false,
			)
		);

		foreach ( $posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}
}

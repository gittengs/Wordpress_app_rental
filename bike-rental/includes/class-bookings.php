<?php
/**
 * Booking logic: creation, validation, and availability checks.
 *
 * @package BikeRental
 */

namespace BikeRental;

defined( 'ABSPATH' ) || exit;

/**
 * Encapsulates everything related to booking a bike.
 */
class Bookings {

	/**
	 * Meta key: rented bike ID.
	 *
	 * @var string
	 */
	const KEY_BIKE_ID = '_booking_bike_id';

	/**
	 * Meta key: customer name.
	 *
	 * @var string
	 */
	const KEY_NAME = '_booking_customer_name';

	/**
	 * Meta key: customer email.
	 *
	 * @var string
	 */
	const KEY_EMAIL = '_booking_customer_email';

	/**
	 * Meta key: start date (Y-m-d).
	 *
	 * @var string
	 */
	const KEY_START = '_booking_start';

	/**
	 * Meta key: end date (Y-m-d).
	 *
	 * @var string
	 */
	const KEY_END = '_booking_end';

	/**
	 * Meta key: booking status.
	 *
	 * @var string
	 */
	const KEY_STATUS = '_booking_status';

	/**
	 * Meta key: customer notes.
	 *
	 * @var string
	 */
	const KEY_NOTES = '_booking_notes';

	/**
	 * Booking statuses.
	 *
	 * @return array<string,string> map of value => label.
	 */
	public static function statuses() {
		return array(
			'pending'   => __( 'Venter', 'bike-rental' ),
			'confirmed' => __( 'Bekreftet', 'bike-rental' ),
			'cancelled' => __( 'Avlyst', 'bike-rental' ),
		);
	}

	/**
	 * Statuses that block availability for the same date range.
	 *
	 * @return string[]
	 */
	public static function blocking_statuses() {
		return array( 'pending', 'confirmed' );
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_meta' ) );
	}

	/**
	 * Register booking meta so it is sanitized and REST-aware.
	 *
	 * @return void
	 */
	public function register_meta() {
		$fields = array(
			array( self::KEY_BIKE_ID, 'integer', 'absint' ),
			array( self::KEY_NAME, 'string', 'sanitize_text_field' ),
			array( self::KEY_EMAIL, 'string', 'sanitize_email' ),
			array( self::KEY_START, 'string', array( __CLASS__, 'sanitize_date' ) ),
			array( self::KEY_END, 'string', array( __CLASS__, 'sanitize_date' ) ),
			array( self::KEY_STATUS, 'string', 'sanitize_key' ),
			array( self::KEY_NOTES, 'string', 'sanitize_textarea_field' ),
		);

		foreach ( $fields as $field ) {
			register_post_meta(
				Post_Types::BOOKING_CPT,
				$field[0],
				array(
					'type'              => $field[1],
					'single'            => true,
					'show_in_rest'      => false,
					'sanitize_callback' => $field[2],
					'auth_callback'     => static function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Normalize a date string to Y-m-d, or return an empty string if invalid.
	 *
	 * @param mixed $value Raw date.
	 * @return string
	 */
	public static function sanitize_date( $value ) {
		if ( ! is_scalar( $value ) ) {
			return '';
		}

		$value = trim( (string) $value );
		$date  = \DateTimeImmutable::createFromFormat( 'Y-m-d', $value );

		if ( ! $date || $date->format( 'Y-m-d' ) !== $value ) {
			return '';
		}

		return $value;
	}

	/**
	 * Determine whether a bike is available for a given date range.
	 *
	 * @param int    $bike_id Bike post ID.
	 * @param string $start   Start date (Y-m-d).
	 * @param string $end     End date (Y-m-d).
	 * @param int    $ignore  Optional booking ID to ignore (for edits).
	 * @return bool
	 */
	public function is_bike_available( $bike_id, $start, $end, $ignore = 0 ) {
		$bike_id = absint( $bike_id );
		$start   = self::sanitize_date( $start );
		$end     = self::sanitize_date( $end );
		$ignore  = absint( $ignore );

		if ( ! $bike_id || ! $start || ! $end || $start > $end ) {
			return false;
		}

		// A bike that is retired or under maintenance can never be booked.
		$bike_status = get_post_meta( $bike_id, Meta::KEY_STATUS, true );
		if ( $bike_status && 'available' !== $bike_status ) {
			return (bool) apply_filters( 'bike_rental_is_bike_available', false, $bike_id, $start, $end );
		}

		$available = ( 0 === count( $this->get_conflicting_bookings( $bike_id, $start, $end, $ignore ) ) );

		/**
		 * Filter the availability verdict for a bike/date range.
		 *
		 * @param bool   $available Whether the bike is available.
		 * @param int    $bike_id   Bike post ID.
		 * @param string $start     Start date.
		 * @param string $end       End date.
		 */
		return (bool) apply_filters( 'bike_rental_is_bike_available', $available, $bike_id, $start, $end );
	}

	/**
	 * Find bookings that overlap a date range for a bike.
	 *
	 * Two ranges overlap when: existing_start <= requested_end AND
	 * existing_end >= requested_start.
	 *
	 * @param int    $bike_id Bike post ID.
	 * @param string $start   Start date (Y-m-d).
	 * @param string $end     End date (Y-m-d).
	 * @param int    $ignore  Optional booking ID to exclude.
	 * @return int[] Booking post IDs.
	 */
	public function get_conflicting_bookings( $bike_id, $start, $end, $ignore = 0 ) {
		$query = new \WP_Query(
			array(
				'post_type'      => Post_Types::BOOKING_CPT,
				'post_status'    => 'publish',
				'posts_per_page' => 100,
				'fields'         => 'ids',
				'post__not_in'   => $ignore ? array( absint( $ignore ) ) : array(),
				'no_found_rows'  => true,
				'meta_query'     => array(
					'relation' => 'AND',
					array(
						'key'     => self::KEY_BIKE_ID,
						'value'   => absint( $bike_id ),
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
					array(
						'key'     => self::KEY_STATUS,
						'value'   => self::blocking_statuses(),
						'compare' => 'IN',
					),
					array(
						'key'     => self::KEY_START,
						'value'   => self::sanitize_date( $end ),
						'compare' => '<=',
						'type'    => 'DATE',
					),
					array(
						'key'     => self::KEY_END,
						'value'   => self::sanitize_date( $start ),
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
			)
		);

		return array_map( 'absint', $query->posts );
	}

	/**
	 * Create a booking.
	 *
	 * @param array $args {
	 *     @type int    $bike_id Bike post ID.
	 *     @type string $name    Customer name.
	 *     @type string $email   Customer email.
	 *     @type string $start   Start date (Y-m-d).
	 *     @type string $end     End date (Y-m-d).
	 *     @type string $notes   Optional notes.
	 *     @type string $status  Optional status (defaults to "pending").
	 * }
	 * @return int|\WP_Error Booking ID on success, WP_Error on failure.
	 */
	public function create_booking( $args ) {
		$defaults = array(
			'bike_id' => 0,
			'name'    => '',
			'email'   => '',
			'start'   => '',
			'end'     => '',
			'notes'   => '',
			'status'  => 'pending',
		);
		$args     = wp_parse_args( $args, $defaults );

		$bike_id = absint( $args['bike_id'] );
		$name    = sanitize_text_field( $args['name'] );
		$email   = sanitize_email( $args['email'] );
		$start   = self::sanitize_date( $args['start'] );
		$end     = self::sanitize_date( $args['end'] );
		$notes   = sanitize_textarea_field( $args['notes'] );
		$status  = array_key_exists( $args['status'], self::statuses() ) ? $args['status'] : 'pending';

		// Validate required data.
		if ( ! $bike_id || Post_Types::BIKE_CPT !== get_post_type( $bike_id ) ) {
			return new \WP_Error( 'invalid_bike', __( 'Vennligst velg en gyldig sykkel.', 'bike-rental' ) );
		}
		if ( '' === $name ) {
			return new \WP_Error( 'invalid_name', __( 'Vennligst skriv inn navnet ditt.', 'bike-rental' ) );
		}
		if ( ! is_email( $email ) ) {
			return new \WP_Error( 'invalid_email', __( 'Vennligst skriv inn en gyldig e-postadresse.', 'bike-rental' ) );
		}
		if ( ! $start || ! $end ) {
			return new \WP_Error( 'invalid_dates', __( 'Vennligst oppgi gyldig start- og sluttdato.', 'bike-rental' ) );
		}
		if ( $start > $end ) {
			return new \WP_Error( 'invalid_range', __( 'Sluttdatoen må være lik eller senere enn startdatoen.', 'bike-rental' ) );
		}
		if ( ! $this->is_bike_available( $bike_id, $start, $end ) ) {
			return new \WP_Error( 'unavailable', __( 'Beklager, denne sykkelen er ikke tilgjengelig for disse datoene.', 'bike-rental' ) );
		}

		$booking_id = wp_insert_post(
			array(
				'post_type'   => Post_Types::BOOKING_CPT,
				'post_status' => 'publish',
				'post_title'  => sprintf(
					/* translators: 1: customer name, 2: bike title */
					__( '%1$s — %2$s', 'bike-rental' ),
					$name,
					get_the_title( $bike_id )
				),
			),
			true
		);

		if ( is_wp_error( $booking_id ) ) {
			return $booking_id;
		}

		update_post_meta( $booking_id, self::KEY_BIKE_ID, $bike_id );
		update_post_meta( $booking_id, self::KEY_NAME, $name );
		update_post_meta( $booking_id, self::KEY_EMAIL, $email );
		update_post_meta( $booking_id, self::KEY_START, $start );
		update_post_meta( $booking_id, self::KEY_END, $end );
		update_post_meta( $booking_id, self::KEY_NOTES, $notes );
		update_post_meta( $booking_id, self::KEY_STATUS, $status );

		/**
		 * Fires after a booking has been created.
		 *
		 * @param int   $booking_id The new booking ID.
		 * @param array $args       The sanitized booking arguments.
		 */
		do_action( 'bike_rental_booking_created', $booking_id, $args );

		return $booking_id;
	}
}

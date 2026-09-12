<?php
/**
 * Bike metadata: registration, admin meta box, and saving.
 *
 * @package BikeRental
 */

namespace BikeRental;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the custom fields attached to the `bike` post type.
 */
class Meta {

	/**
	 * Meta key: daily rental rate.
	 *
	 * @var string
	 */
	const KEY_DAILY_RATE = '_bike_daily_rate';

	/**
	 * Meta key: hourly rental rate.
	 *
	 * @var string
	 */
	const KEY_HOURLY_RATE = '_bike_hourly_rate';

	/**
	 * Meta key: bike size (e.g. S / M / L).
	 *
	 * @var string
	 */
	const KEY_SIZE = '_bike_size';

	/**
	 * Meta key: inventory / fleet code.
	 *
	 * @var string
	 */
	const KEY_CODE = '_bike_code';

	/**
	 * Meta key: current status.
	 *
	 * @var string
	 */
	const KEY_STATUS = '_bike_status';

	/**
	 * Nonce action used by the meta box.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'bike_rental_save_bike_meta';

	/**
	 * Allowed bike status values.
	 *
	 * @return array<string,string> map of value => label.
	 */
	public static function statuses() {
		return array(
			'available'   => __( 'Tilgjengelig', 'bike-rental' ),
			'rented'      => __( 'Utleid', 'bike-rental' ),
			'maintenance' => __( 'Vedlikehold', 'bike-rental' ),
		);
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_meta' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_' . Post_Types::BIKE_CPT, array( $this, 'save_meta' ), 10, 2 );
	}

	/**
	 * Register the bike meta fields for REST and sanitization.
	 *
	 * @return void
	 */
	public function register_meta() {
		$fields = array(
			array( self::KEY_DAILY_RATE, 'number', array( __CLASS__, 'sanitize_number' ) ),
			array( self::KEY_HOURLY_RATE, 'number', array( __CLASS__, 'sanitize_number' ) ),
			array( self::KEY_SIZE, 'string', 'sanitize_text_field' ),
			array( self::KEY_CODE, 'string', 'sanitize_text_field' ),
			array( self::KEY_STATUS, 'string', 'sanitize_key' ),
		);

		foreach ( $fields as $field ) {
			register_post_meta(
				Post_Types::BIKE_CPT,
				$field[0],
				array(
					'type'              => $field[1],
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => $field[2],
					'auth_callback'     => static function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Register the "Bike Details" meta box.
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			'bike_rental_details',
			__( 'Sykkeldetaljer', 'bike-rental' ),
			array( $this, 'render_meta_box' ),
			Post_Types::BIKE_CPT,
			'normal',
			'high'
		);
	}

	/**
	 * Render the meta box fields.
	 *
	 * @param \WP_Post $post Current post object.
	 * @return void
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, 'bike_rental_meta_nonce' );

		$daily_rate  = get_post_meta( $post->ID, self::KEY_DAILY_RATE, true );
		$hourly_rate = get_post_meta( $post->ID, self::KEY_HOURLY_RATE, true );
		$size        = get_post_meta( $post->ID, self::KEY_SIZE, true );
		$code        = get_post_meta( $post->ID, self::KEY_CODE, true );
		$status      = get_post_meta( $post->ID, self::KEY_STATUS, true );
		$status      = $status ? $status : 'available';
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="bike_rental_code"><?php esc_html_e( 'Flåtekode', 'bike-rental' ); ?></label>
				</th>
				<td>
					<input type="text" id="bike_rental_code" name="bike_rental_code"
						value="<?php echo esc_attr( $code ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'En unik intern identifikator for denne sykkelen.', 'bike-rental' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bike_rental_size"><?php esc_html_e( 'Rammestørrelse', 'bike-rental' ); ?></label>
				</th>
				<td>
					<input type="text" id="bike_rental_size" name="bike_rental_size"
						value="<?php echo esc_attr( $size ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'f.eks. S, M, L eller 52 cm.', 'bike-rental' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bike_rental_hourly_rate"><?php esc_html_e( 'Timepris', 'bike-rental' ); ?></label>
				</th>
				<td>
					<input type="number" step="0.01" min="0" id="bike_rental_hourly_rate"
						name="bike_rental_hourly_rate" value="<?php echo esc_attr( $hourly_rate ); ?>"
						class="small-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bike_rental_daily_rate"><?php esc_html_e( 'Dagpris', 'bike-rental' ); ?></label>
				</th>
				<td>
					<input type="number" step="0.01" min="0" id="bike_rental_daily_rate"
						name="bike_rental_daily_rate" value="<?php echo esc_attr( $daily_rate ); ?>"
						class="small-text" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="bike_rental_status"><?php esc_html_e( 'Status', 'bike-rental' ); ?></label>
				</th>
				<td>
					<select id="bike_rental_status" name="bike_rental_status">
						<?php foreach ( self::statuses() as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $status, $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Persist the meta box values.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function save_meta( $post_id, $post ) {
		// Bail on autosaves, revisions, and bulk edits.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Verify nonce.
		if ( ! isset( $_POST['bike_rental_meta_nonce'] ) ) {
			return;
		}
		$nonce = sanitize_text_field( wp_unslash( $_POST['bike_rental_meta_nonce'] ) );
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		// Verify capability.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Sanitize and save each field.
		if ( isset( $_POST['bike_rental_code'] ) ) {
			update_post_meta( $post_id, self::KEY_CODE, sanitize_text_field( wp_unslash( $_POST['bike_rental_code'] ) ) );
		}
		if ( isset( $_POST['bike_rental_size'] ) ) {
			update_post_meta( $post_id, self::KEY_SIZE, sanitize_text_field( wp_unslash( $_POST['bike_rental_size'] ) ) );
		}
		if ( isset( $_POST['bike_rental_hourly_rate'] ) ) {
			update_post_meta( $post_id, self::KEY_HOURLY_RATE, self::sanitize_number( wp_unslash( $_POST['bike_rental_hourly_rate'] ) ) );
		}
		if ( isset( $_POST['bike_rental_daily_rate'] ) ) {
			update_post_meta( $post_id, self::KEY_DAILY_RATE, self::sanitize_number( wp_unslash( $_POST['bike_rental_daily_rate'] ) ) );
		}
		if ( isset( $_POST['bike_rental_status'] ) ) {
			$status = sanitize_key( wp_unslash( $_POST['bike_rental_status'] ) );
			if ( array_key_exists( $status, self::statuses() ) ) {
				update_post_meta( $post_id, self::KEY_STATUS, $status );
			}
		}
	}

	/**
	 * Sanitize a monetary rate into a non-negative float.
	 *
	 * @param mixed $value Raw value.
	 * @return float
	 */
	public static function sanitize_number( $value ) {
		$value = is_scalar( $value ) ? (float) $value : 0.0;

		return max( 0.0, round( $value, 2 ) );
	}
}

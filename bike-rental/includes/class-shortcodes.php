<?php
/**
 * Front-end shortcodes: bike listings and the booking form.
 *
 * @package BikeRental
 */

namespace BikeRental;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and renders the plugin's public shortcodes.
 */
class Shortcodes {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_shortcode( 'bike_listing', array( $this, 'render_listing' ) );
		add_shortcode( 'bike_booking_form', array( $this, 'render_booking_form' ) );
	}

	/**
	 * Render the [bike_listing] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_listing( $atts ) {
		$atts = shortcode_atts(
			array(
				'type'    => '',
				'limit'   => 12,
				'columns' => 3,
			),
			$atts,
			'bike_listing'
		);

		$query_args = array(
			'post_type'      => Post_Types::BIKE_CPT,
			'post_status'    => 'publish',
			'posts_per_page' => absint( $atts['limit'] ),
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		if ( ! empty( $atts['type'] ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => Post_Types::BIKE_TYPE_TAX,
					'field'    => 'slug',
					'terms'    => sanitize_title( $atts['type'] ),
				),
			);
		}

		$bikes = new \WP_Query( $query_args );

		if ( ! $bikes->have_posts() ) {
			return '<p class="bike-rental-empty">' . esc_html__( 'No bikes are available right now.', 'bike-rental' ) . '</p>';
		}

		$columns = max( 1, min( 4, absint( $atts['columns'] ) ) );

		ob_start();
		echo '<div class="bike-rental-grid bike-rental-columns-' . esc_attr( $columns ) . '">';
		while ( $bikes->have_posts() ) {
			$bikes->the_post();
			$bike_id    = get_the_ID();
			$daily_rate = get_post_meta( $bike_id, Meta::KEY_DAILY_RATE, true );
			$status     = get_post_meta( $bike_id, Meta::KEY_STATUS, true );
			$status     = $status ? $status : 'available';
			?>
			<article class="bike-rental-card bike-rental-status-<?php echo esc_attr( $status ); ?>">
				<?php if ( has_post_thumbnail( $bike_id ) ) : ?>
					<a class="bike-rental-card__image" href="<?php the_permalink(); ?>">
						<?php echo get_the_post_thumbnail( $bike_id, 'medium' ); ?>
					</a>
				<?php endif; ?>
				<h3 class="bike-rental-card__title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h3>
				<div class="bike-rental-card__excerpt"><?php the_excerpt(); ?></div>
				<p class="bike-rental-card__meta">
					<?php if ( '' !== $daily_rate ) : ?>
						<span class="bike-rental-card__price">
							<?php
							/* translators: %s: formatted daily rate. */
							printf( esc_html__( '%s / day', 'bike-rental' ), esc_html( $this->format_price( $daily_rate ) ) );
							?>
						</span>
					<?php endif; ?>
					<span class="bike-rental-card__status">
						<?php echo esc_html( $this->status_label( $status ) ); ?>
					</span>
				</p>
				<p class="bike-rental-card__actions">
					<a class="bike-rental-button" href="<?php echo esc_url( $this->booking_page_url( $bike_id ) ); ?>">
						<?php esc_html_e( 'Book this bike', 'bike-rental' ); ?>
					</a>
				</p>
			</article>
			<?php
		}
		echo '</div>';
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Translate a status slug into a human-readable label.
	 *
	 * @param string $status Status slug.
	 * @return string
	 */
	private function status_label( $status ) {
		$statuses = Meta::statuses();

		return isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
	}

	/**
	 * Format a numeric price for display.
	 *
	 * @param mixed $amount Amount.
	 * @return string
	 */
	private function format_price( $amount ) {
		$symbol = get_option( 'bike_rental_currency_symbol', '$' );
		$symbol = apply_filters( 'bike_rental_currency_symbol', $symbol );

		return $symbol . number_format_i18n( (float) $amount, 2 );
	}

	/**
	 * Build the URL of the page holding the booking form for a bike.
	 *
	 * @param int $bike_id Bike post ID.
	 * @return string
	 */
	private function booking_page_url( $bike_id ) {
		$base = get_permalink();
		$page = get_option( 'bike_rental_booking_page', '' );

		if ( $page && (int) $page !== (int) get_the_ID() ) {
			$permalink = get_permalink( (int) $page );
			if ( $permalink ) {
				$base = $permalink;
			}
		}

		return add_query_arg( 'bike_id', absint( $bike_id ), $base );
	}

	/**
	 * Render the [bike_booking_form] shortcode and handle submissions.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_booking_form( $atts ) {
		$atts = shortcode_atts(
			array(
				'bike_id' => 0,
			),
			$atts,
			'bike_booking_form'
		);

		$bike_id = absint( $atts['bike_id'] );
		if ( ! $bike_id && isset( $_GET['bike_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display parameter.
			$bike_id = absint( wp_unslash( $_GET['bike_id'] ) );
		}

		if ( ! $bike_id || Post_Types::BIKE_CPT !== get_post_type( $bike_id ) ) {
			return '<p class="bike-rental-error">' . esc_html__( 'No bike was selected.', 'bike-rental' ) . '</p>';
		}

		$message = '';

		// Handle submission.
		if ( isset( $_POST['bike_rental_booking_submit'] ) ) {
			$nonce_ok = isset( $_POST['bike_rental_booking_nonce'] )
				&& wp_verify_nonce(
					sanitize_text_field( wp_unslash( $_POST['bike_rental_booking_nonce'] ) ),
					'bike_rental_submit_booking'
				);

			if ( ! $nonce_ok ) {
				$message = $this->notice( 'error', __( 'Security check failed. Please try again.', 'bike-rental' ) );
			} else {
				$bookings = new Bookings();
				$result   = $bookings->create_booking(
					array(
						'bike_id' => $bike_id,
						'name'    => isset( $_POST['bike_rental_name'] ) ? wp_unslash( $_POST['bike_rental_name'] ) : '',
						'email'   => isset( $_POST['bike_rental_email'] ) ? wp_unslash( $_POST['bike_rental_email'] ) : '',
						'start'   => isset( $_POST['bike_rental_start'] ) ? wp_unslash( $_POST['bike_rental_start'] ) : '',
						'end'     => isset( $_POST['bike_rental_end'] ) ? wp_unslash( $_POST['bike_rental_end'] ) : '',
						'notes'   => isset( $_POST['bike_rental_notes'] ) ? wp_unslash( $_POST['bike_rental_notes'] ) : '',
					)
				);

				if ( is_wp_error( $result ) ) {
					$message = $this->notice( 'error', $result->get_error_message() );
				} else {
					$message = $this->notice( 'success', __( 'Thank you! Your booking request has been received.', 'bike-rental' ) );
				}
			}
		}

		return $message . $this->render_form( $bike_id );
	}

	/**
	 * Build a front-end notice element.
	 *
	 * @param string $type    "success" or "error".
	 * @param string $text    Message text.
	 * @return string
	 */
	private function notice( $type, $text ) {
		$class = 'success' === $type ? 'bike-rental-success' : 'bike-rental-error';

		return '<p class="' . esc_attr( $class ) . '">' . esc_html( $text ) . '</p>';
	}

	/**
	 * Render the booking form markup.
	 *
	 * @param int $bike_id Bike post ID.
	 * @return string
	 */
	private function render_form( $bike_id ) {
		$today = current_time( 'Y-m-d' );

		$prefill_name  = isset( $_POST['bike_rental_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bike_rental_name'] ) ) : '';
		$prefill_email = isset( $_POST['bike_rental_email'] ) ? sanitize_text_field( wp_unslash( $_POST['bike_rental_email'] ) ) : '';
		$prefill_start = isset( $_POST['bike_rental_start'] ) ? sanitize_text_field( wp_unslash( $_POST['bike_rental_start'] ) ) : '';
		$prefill_end   = isset( $_POST['bike_rental_end'] ) ? sanitize_text_field( wp_unslash( $_POST['bike_rental_end'] ) ) : '';
		$prefill_notes = isset( $_POST['bike_rental_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bike_rental_notes'] ) ) : '';

		ob_start();
		?>
		<form class="bike-rental-form" method="post"
			action="<?php echo esc_url( get_permalink() ); ?>">
			<h3 class="bike-rental-form__title">
				<?php
				/* translators: %s: bike title. */
				printf( esc_html__( 'Book: %s', 'bike-rental' ), esc_html( get_the_title( $bike_id ) ) );
				?>
			</h3>

			<input type="hidden" name="bike_id" value="<?php echo esc_attr( $bike_id ); ?>" />
			<?php wp_nonce_field( 'bike_rental_submit_booking', 'bike_rental_booking_nonce' ); ?>

			<p class="bike-rental-field">
				<label for="bike_rental_name"><?php esc_html_e( 'Your name', 'bike-rental' ); ?> <span aria-hidden="true">*</span></label>
				<input type="text" id="bike_rental_name" name="bike_rental_name"
					value="<?php echo esc_attr( $prefill_name ); ?>" required />
			</p>

			<p class="bike-rental-field">
				<label for="bike_rental_email"><?php esc_html_e( 'Email address', 'bike-rental' ); ?> <span aria-hidden="true">*</span></label>
				<input type="email" id="bike_rental_email" name="bike_rental_email"
					value="<?php echo esc_attr( $prefill_email ); ?>" required />
			</p>

			<p class="bike-rental-field">
				<label for="bike_rental_start"><?php esc_html_e( 'Start date', 'bike-rental' ); ?> <span aria-hidden="true">*</span></label>
				<input type="date" id="bike_rental_start" name="bike_rental_start"
					min="<?php echo esc_attr( $today ); ?>"
					value="<?php echo esc_attr( $prefill_start ); ?>" required />
			</p>

			<p class="bike-rental-field">
				<label for="bike_rental_end"><?php esc_html_e( 'End date', 'bike-rental' ); ?> <span aria-hidden="true">*</span></label>
				<input type="date" id="bike_rental_end" name="bike_rental_end"
					min="<?php echo esc_attr( $today ); ?>"
					value="<?php echo esc_attr( $prefill_end ); ?>" required />
			</p>

			<p class="bike-rental-field">
				<label for="bike_rental_notes"><?php esc_html_e( 'Notes (optional)', 'bike-rental' ); ?></label>
				<textarea id="bike_rental_notes" name="bike_rental_notes" rows="3"><?php echo esc_textarea( $prefill_notes ); ?></textarea>
			</p>

			<p class="bike-rental-field">
				<button type="submit" name="bike_rental_booking_submit" value="1" class="bike-rental-button">
					<?php esc_html_e( 'Request booking', 'bike-rental' ); ?>
				</button>
			</p>
		</form>
		<?php
		return ob_get_clean();
	}
}

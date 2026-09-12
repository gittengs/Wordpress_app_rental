<?php
/**
 * Admin settings page.
 *
 * @package BikeRental
 */

namespace BikeRental;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the plugin settings screen using the WordPress Settings API.
 */
class Settings {

	/**
	 * Option group name.
	 *
	 * @var string
	 */
	const OPTION_GROUP = 'bike_rental_settings';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'bike-rental-settings';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add the settings page under the Bikes menu.
	 *
	 * @return void
	 */
	public function add_menu() {
		add_submenu_page(
			'edit.php?post_type=' . Post_Types::BIKE_CPT,
			__( 'Bike Rental Settings', 'bike-rental' ),
			__( 'Settings', 'bike-rental' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register the settings and their fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			self::OPTION_GROUP,
			'bike_rental_currency_symbol',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '$',
			)
		);
		register_setting(
			self::OPTION_GROUP,
			'bike_rental_business_name',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
		register_setting(
			self::OPTION_GROUP,
			'bike_rental_booking_page',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);
		register_setting(
			self::OPTION_GROUP,
			'bike_rental_terms_text',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
				'default'           => '',
			)
		);

		add_settings_section(
			'bike_rental_general',
			__( 'General', 'bike-rental' ),
			array( $this, 'render_section' ),
			self::PAGE_SLUG
		);

		$fields = array(
			'bike_rental_business_name'   => __( 'Business name', 'bike-rental' ),
			'bike_rental_currency_symbol' => __( 'Currency symbol', 'bike-rental' ),
			'bike_rental_booking_page'    => __( 'Booking page', 'bike-rental' ),
			'bike_rental_terms_text'      => __( 'Rental terms', 'bike-rental' ),
		);

		foreach ( $fields as $key => $label ) {
			add_settings_field(
				$key,
				$label,
				array( $this, 'render_field' ),
				self::PAGE_SLUG,
				'bike_rental_general',
				array(
					'label_for' => $key,
					'key'       => $key,
				)
			);
		}
	}

	/**
	 * Render the section description.
	 *
	 * @return void
	 */
	public function render_section() {
		echo '<p>' . esc_html__( 'Configure how Bike Rental behaves on your site.', 'bike-rental' ) . '</p>';
	}

	/**
	 * Render an individual settings field.
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public function render_field( $args ) {
		$key   = $args['key'];
		$value = get_option( $key, '' );

		if ( 'bike_rental_booking_page' === $key ) {
			wp_dropdown_pages(
				array(
					'name'              => $key,
					'id'                => $key,
					'selected'          => absint( $value ),
					'show_option_none'  => __( '— Use current page —', 'bike-rental' ),
					'option_none_value' => 0,
				)
			);
			echo '<p class="description">' . esc_html__( 'The page that contains the [bike_booking_form] shortcode.', 'bike-rental' ) . '</p>';
			return;
		}

		if ( 'bike_rental_terms_text' === $key ) {
			printf(
				'<textarea id="%1$s" name="%1$s" rows="5" class="large-text">%2$s</textarea>',
				esc_attr( $key ),
				esc_textarea( $value )
			);
			return;
		}

		printf(
			'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" />',
			esc_attr( $key ),
			esc_attr( $value )
		);
	}

	/**
	 * Render the settings page wrapper.
	 *
	 * @return void
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}

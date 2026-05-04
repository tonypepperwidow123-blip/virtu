<?php
/**
 * Shortcode handler for VirtuConnect.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Shortcode
 *
 * Registers and renders the [virtu_connect] shortcode.
 */
class Virtu_Shortcode {

	/**
	 * Constructor — register shortcode.
	 */
	public function __construct() {
		add_shortcode( 'virtu_connect', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Render the [virtu_connect] shortcode.
	 *
	 * Enqueues public CSS/JS and outputs the product boxes and modal form.
	 *
	 * @param array $atts Shortcode attributes (not currently used).
	 * @return string The rendered HTML.
	 */
	public function render_shortcode( $atts = array() ) {
		if ( 'yes' !== get_option( 'virtu_enabled', 'yes' ) ) {
			return '';
		}

		// Enqueue assets.
		wp_enqueue_style(
			'virtu-public-css',
			VIRTU_URL . 'public/assets/virtu-public.css',
			array(),
			VIRTU_VERSION
		);

		wp_enqueue_script(
			'virtu-public-js',
			VIRTU_URL . 'public/assets/virtu-public.js',
			array(),
			VIRTU_VERSION,
			true
		);

		// Build localized data.
		$product_id    = get_the_ID();
		$product_name  = get_the_title();
		$product_url   = get_permalink();
		$product_price = '';

		if ( function_exists( 'wc_get_product' ) && $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$product_price = $product->get_price();
			}
		}

		wp_localize_script( 'virtu-public-js', 'virtucConnect', array(
			'ajax_url'     => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'virtu_form_nonce' ),
			'wa_number'    => get_option( 'virtu_wa_number', '' ),
			'wa_template'  => get_option( 'virtu_wa_message_template', '' ),
			'product_id'   => $product_id,
			'product_name' => $product_name,
			'product_url'  => $product_url,
			'product_price' => $product_price,
			'show_date'    => get_option( 'virtu_form_show_date', 'yes' ),
			'show_time'    => get_option( 'virtu_form_show_time', 'yes' ),
			'show_message' => get_option( 'virtu_form_show_message', 'yes' ),
			'strings'      => array(
				'submitting'    => __( 'Submitting...', 'virtu-connect' ),
				'success'       => __( 'Request submitted successfully!', 'virtu-connect' ),
				'error'         => __( 'Something went wrong. Please try again.', 'virtu-connect' ),
				'required'      => __( 'Please fill all required fields.', 'virtu-connect' ),
				'invalid_email' => __( 'Please enter a valid email address.', 'virtu-connect' ),
			),
		) );

		ob_start();
		include VIRTU_PATH . 'public/views/product-boxes.php';
		include VIRTU_PATH . 'public/views/modal-form.php';
		return ob_get_clean();
	}
}

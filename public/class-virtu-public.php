<?php
/**
 * Public-facing functionality.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Public
 *
 * Handles front-end display, asset enqueueing, and WooCommerce hook integration.
 */
class Virtu_Public {

	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_action( 'wp', array( $this, 'init_display' ) );
	}

	/**
	 * Initialize display hooks after WP is fully loaded (so is_product() works).
	 */
	public function init_display() {
		if ( 'yes' !== get_option( 'virtu_enabled', 'yes' ) ) {
			return;
		}

		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		// Enqueue assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Display boxes on chosen hook.
		$hook = get_option( 'virtu_display_hook', 'woocommerce_single_product_summary' );

		// Fallback for old setting
		if ( 'woocommerce_after_add_to_cart_button' === $hook ) {
			$hook = 'woocommerce_after_add_to_cart_form';
		}

		if ( 'shortcode_only' !== $hook ) {
			$priority = absint( get_option( 'virtu_display_priority', 35 ) );
			add_action( $hook, array( $this, 'render_product_boxes' ), $priority );
		}

		// Add modal to footer.
		add_action( 'wp_footer', array( $this, 'render_modal' ) );
	}

	/**
	 * Enqueue public CSS and JS.
	 */
	public function enqueue_assets() {
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

		$product_id    = get_the_ID();
		$product_price = '';

		if ( function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				$product_price = $product->get_price();
			}
		}

		wp_localize_script( 'virtu-public-js', 'virtucConnect', array(
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'virtu_form_nonce' ),
			'wa_number'     => get_option( 'virtu_wa_number', '' ),
			'wa_template'   => get_option( 'virtu_wa_message_template', '' ),
			'product_id'    => $product_id,
			'product_name'  => get_the_title(),
			'product_url'   => get_permalink(),
			'product_price' => $product_price,
			'show_date'     => get_option( 'virtu_form_show_date', 'yes' ),
			'show_time'     => get_option( 'virtu_form_show_time', 'yes' ),
			'show_message'  => get_option( 'virtu_form_show_message', 'yes' ),
			'strings'       => array(
				'submitting'    => __( 'Submitting...', 'virtu-connect' ),
				'success'       => __( 'Request submitted successfully!', 'virtu-connect' ),
				'error'         => __( 'Something went wrong. Please try again.', 'virtu-connect' ),
				'required'      => __( 'Please fill all required fields.', 'virtu-connect' ),
				'invalid_email' => __( 'Please enter a valid email address.', 'virtu-connect' ),
			),
		) );
	}

	/**
	 * Render the product boxes.
	 */
	public function render_product_boxes() {
		include VIRTU_PATH . 'public/views/product-boxes.php';
	}

	/**
	 * Render the modal form in the footer.
	 */
	public function render_modal() {
		include VIRTU_PATH . 'public/views/modal-form.php';
	}
}

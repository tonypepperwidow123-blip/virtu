<?php
/**
 * AJAX handler for form submissions and lead status updates.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Ajax
 *
 * Registers and handles all AJAX actions for the plugin.
 */
class Virtu_Ajax {

	/**
	 * Constructor — register AJAX hooks.
	 */
	public function __construct() {
		// Public form submission.
		add_action( 'wp_ajax_virtu_submit_form', array( $this, 'handle_form_submission' ) );
		add_action( 'wp_ajax_nopriv_virtu_submit_form', array( $this, 'handle_form_submission' ) );

		// Admin lead status update.
		add_action( 'wp_ajax_virtu_update_lead_status', array( $this, 'handle_update_lead_status' ) );
	}

	/**
	 * Handle the video call form submission via AJAX.
	 *
	 * Validates nonce, sanitizes input, inserts lead into the database,
	 * sends admin notification email, and optionally sends auto-reply.
	 */
	public function handle_form_submission() {
		// 1. Verify nonce.
		if ( ! check_ajax_referer( 'virtu_form_nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Security check failed. Please refresh and try again.', 'virtu-connect' ) );
		}

		// 2. Sanitize all POST fields.
		$name          = sanitize_text_field( wp_unslash( isset( $_POST['virtu_name'] ) ? $_POST['virtu_name'] : '' ) );
		$email         = sanitize_email( wp_unslash( isset( $_POST['virtu_email'] ) ? $_POST['virtu_email'] : '' ) );
		$phone         = sanitize_text_field( wp_unslash( isset( $_POST['virtu_phone'] ) ? $_POST['virtu_phone'] : '' ) );
		$date          = sanitize_text_field( wp_unslash( isset( $_POST['virtu_date'] ) ? $_POST['virtu_date'] : '' ) );
		$time          = sanitize_text_field( wp_unslash( isset( $_POST['virtu_time'] ) ? $_POST['virtu_time'] : '' ) );
		$message       = sanitize_textarea_field( wp_unslash( isset( $_POST['virtu_message'] ) ? $_POST['virtu_message'] : '' ) );
		$product_id    = absint( isset( $_POST['product_id'] ) ? $_POST['product_id'] : 0 );
		$product_name  = sanitize_text_field( wp_unslash( isset( $_POST['product_name'] ) ? $_POST['product_name'] : '' ) );
		$product_url   = esc_url_raw( wp_unslash( isset( $_POST['product_url'] ) ? $_POST['product_url'] : '' ) );
		$product_price = sanitize_text_field( wp_unslash( isset( $_POST['product_price'] ) ? $_POST['product_price'] : '' ) );

		// 3. Validate required fields.
		if ( empty( $name ) || empty( $email ) || empty( $phone ) ) {
			wp_send_json_error( __( 'Please fill all required fields.', 'virtu-connect' ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( __( 'Please enter a valid email address.', 'virtu-connect' ) );
		}

		// 4. Insert into database.
		global $wpdb;
		$table_name = $wpdb->prefix . 'virtu_leads';

		$inserted = $wpdb->insert(
			$table_name,
			array(
				'product_id'     => $product_id,
				'product_name'   => $product_name,
				'product_url'    => $product_url,
				'product_price'  => $product_price,
				'customer_name'  => $name,
				'customer_email' => $email,
				'customer_phone' => $phone,
				'preferred_date' => ! empty( $date ) ? $date : null,
				'preferred_time' => $time,
				'message'        => $message,
				'status'         => 'new',
				'created_at'     => current_time( 'mysql' ),
			),
			array(
				'%d', // product_id.
				'%s', // product_name.
				'%s', // product_url.
				'%s', // product_price.
				'%s', // customer_name.
				'%s', // customer_email.
				'%s', // customer_phone.
				'%s', // preferred_date.
				'%s', // preferred_time.
				'%s', // message.
				'%s', // status.
				'%s', // created_at.
			)
		);

		if ( false === $inserted ) {
			wp_send_json_error( __( 'Failed to save your request. Please try again.', 'virtu-connect' ) );
		}

		// Build lead data array for emails.
		$lead_data = array(
			'id'             => $wpdb->insert_id,
			'product_id'     => $product_id,
			'product_name'   => $product_name,
			'product_url'    => $product_url,
			'product_price'  => $product_price,
			'customer_name'  => $name,
			'customer_email' => $email,
			'customer_phone' => $phone,
			'preferred_date' => $date,
			'preferred_time' => $time,
			'message'        => $message,
		);

		// 5. Send admin notification email if enabled.
		if ( 'yes' === get_option( 'virtu_enable_admin_email', 'yes' ) ) {
			Virtu_Email::send_notification( $lead_data );
		}

		// 6. Send auto-reply if enabled.
		if ( 'yes' === get_option( 'virtu_auto_reply', 'yes' ) ) {
			Virtu_Email::send_auto_reply( $lead_data );
		}

		// 7. Return success.
		wp_send_json_success(
			array(
				'message' => __( 'Your request has been submitted! We will contact you soon.', 'virtu-connect' ),
			)
		);
	}

	/**
	 * Handle lead status update via AJAX from the admin leads table.
	 */
	public function handle_update_lead_status() {
		// Verify nonce.
		if ( ! check_ajax_referer( 'virtu_leads_nonce', 'nonce', false ) ) {
			wp_send_json_error( __( 'Security check failed.', 'virtu-connect' ) );
		}

		// Check capability.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'virtu-connect' ) );
		}

		$lead_id = absint( isset( $_POST['lead_id'] ) ? $_POST['lead_id'] : 0 );
		$status  = sanitize_text_field( wp_unslash( isset( $_POST['status'] ) ? $_POST['status'] : '' ) );

		// Validate status value.
		$allowed_statuses = array( 'new', 'contacted', 'closed' );
		if ( ! in_array( $status, $allowed_statuses, true ) ) {
			wp_send_json_error( __( 'Invalid status value.', 'virtu-connect' ) );
		}

		if ( empty( $lead_id ) ) {
			wp_send_json_error( __( 'Invalid lead ID.', 'virtu-connect' ) );
		}

		$result = Virtu_Leads::update_lead_status( $lead_id, $status );

		if ( $result ) {
			wp_send_json_success(
				array(
					'message' => __( 'Status updated successfully.', 'virtu-connect' ),
				)
			);
		} else {
			wp_send_json_error( __( 'Failed to update status.', 'virtu-connect' ) );
		}
	}
}

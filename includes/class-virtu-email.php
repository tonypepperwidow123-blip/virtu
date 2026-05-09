<?php
/**
 * Email notification handler for VirtuConnect.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Email
 *
 * Handles sending admin notification and customer auto-reply emails.
 * Supports both default wp_mail() and the Resend API.
 */
class Virtu_Email {

	/**
	 * Send admin notification email when a new lead is submitted.
	 *
	 * @param array $lead_data Associative array of lead information.
	 */
	public static function send_notification( $lead_data ) {
		$to = get_option( 'virtu_admin_email', get_option( 'admin_email' ) );
		if ( empty( $to ) ) {
			$to = get_option( 'admin_email' );
		}

		// Build subject from template.
		$subject_template = get_option( 'virtu_email_subject', 'New Video Call Request - {product_name}' );
		$subject          = str_replace(
			array( '{product_name}', '{customer_name}', '{date}' ),
			array(
				isset( $lead_data['product_name'] ) ? $lead_data['product_name'] : '',
				isset( $lead_data['customer_name'] ) ? $lead_data['customer_name'] : '',
				isset( $lead_data['preferred_date'] ) ? $lead_data['preferred_date'] : '',
			),
			$subject_template
		);

		// Optional product thumbnail HTML.
		$product_image = '';
		if ( ! empty( $lead_data['product_id'] ) ) {
			$thumbnail_url = get_the_post_thumbnail_url( (int) $lead_data['product_id'], 'thumbnail' );
			if ( $thumbnail_url ) {
				$product_image = '<img src="' . esc_url( $thumbnail_url ) . '" alt="'
					. esc_attr( isset( $lead_data['product_name'] ) ? $lead_data['product_name'] : '' )
					. '" style="max-width:100px;height:auto;border-radius:8px;margin-bottom:12px;" />';
			}
		}

		// Safely retrieve all fields.
		$customer_name  = isset( $lead_data['customer_name'] ) ? $lead_data['customer_name'] : '';
		$customer_email = isset( $lead_data['customer_email'] ) ? $lead_data['customer_email'] : '';
		$customer_phone = isset( $lead_data['customer_phone'] ) ? $lead_data['customer_phone'] : '';
		$product_name   = isset( $lead_data['product_name'] ) ? $lead_data['product_name'] : '';
		$product_url    = isset( $lead_data['product_url'] ) ? $lead_data['product_url'] : '';
		$product_price  = isset( $lead_data['product_price'] ) ? $lead_data['product_price'] : '';

		// Build HTML body.
		$body  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>';
		$body .= '<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f4f7;padding:20px;">';
		$body .= '<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">';
		$body .= '<div style="background:#3c1a0e;padding:24px 32px;">';
		$body .= '<h1 style="color:#ffffff;margin:0;font-size:20px;">&#x1F4F9; New Video Call Request</h1>';
		$body .= '</div>';
		$body .= '<div style="padding:32px;">';
		$body .= '<h2 style="color:#333;font-size:16px;margin:0 0 16px;border-bottom:2px solid #f0f0f0;padding-bottom:8px;">Customer Details</h2>';
		$body .= '<table style="width:100%;border-collapse:collapse;margin-bottom:24px;">';
		$body .= '<tr><td style="padding:8px 12px;color:#666;font-size:14px;width:140px;">Name</td><td style="padding:8px 12px;color:#333;font-size:14px;font-weight:600;">' . esc_html( $customer_name ) . '</td></tr>';
		$body .= '<tr style="background:#fafafa;"><td style="padding:8px 12px;color:#666;font-size:14px;">Email</td><td style="padding:8px 12px;color:#333;font-size:14px;"><a href="mailto:' . esc_attr( $customer_email ) . '" style="color:#3c1a0e;">' . esc_html( $customer_email ) . '</a></td></tr>';
		$body .= '<tr><td style="padding:8px 12px;color:#666;font-size:14px;">Phone</td><td style="padding:8px 12px;color:#333;font-size:14px;"><a href="tel:' . esc_attr( $customer_phone ) . '" style="color:#3c1a0e;">' . esc_html( $customer_phone ) . '</a></td></tr>';

		if ( ! empty( $lead_data['preferred_date'] ) ) {
			$body .= '<tr style="background:#fafafa;"><td style="padding:8px 12px;color:#666;font-size:14px;">Preferred Date</td><td style="padding:8px 12px;color:#333;font-size:14px;">' . esc_html( $lead_data['preferred_date'] ) . '</td></tr>';
		}
		if ( ! empty( $lead_data['preferred_time'] ) ) {
			$body .= '<tr><td style="padding:8px 12px;color:#666;font-size:14px;">Preferred Time</td><td style="padding:8px 12px;color:#333;font-size:14px;">' . esc_html( $lead_data['preferred_time'] ) . '</td></tr>';
		}
		if ( ! empty( $lead_data['message'] ) ) {
			$body .= '<tr style="background:#fafafa;"><td style="padding:8px 12px;color:#666;font-size:14px;">Message</td><td style="padding:8px 12px;color:#333;font-size:14px;">' . esc_html( $lead_data['message'] ) . '</td></tr>';
		}

		$body .= '</table>';
		$body .= '<h2 style="color:#333;font-size:16px;margin:0 0 16px;border-bottom:2px solid #f0f0f0;padding-bottom:8px;">Product Details</h2>';
		$body .= '<div style="background:#fdf5e6;border-radius:8px;padding:16px;">';
		if ( ! empty( $product_image ) ) {
			$body .= '<div>' . $product_image . '</div>';
		}
		$body .= '<p style="margin:0 0 4px;font-size:15px;font-weight:700;color:#333;">';
		if ( ! empty( $product_url ) ) {
			$body .= '<a href="' . esc_url( $product_url ) . '" style="color:#3c1a0e;text-decoration:none;">' . esc_html( $product_name ) . '</a>';
		} else {
			$body .= esc_html( $product_name );
		}
		$body .= '</p>';
		if ( ! empty( $product_price ) ) {
			$body .= '<p style="margin:0;font-size:14px;color:#555;">Price: &#8377;' . esc_html( $product_price ) . '</p>';
		}
		$body .= '</div>';
		$body .= '</div>';
		$body .= '<div style="padding:16px 32px;background:#fafafa;border-top:1px solid #eee;text-align:center;"><p style="margin:0;font-size:12px;color:#999;">Sent by VirtuConnect for WooCommerce</p></div>';
		$body .= '</div></body></html>';

		// Pass customer email as Reply-To so admin can reply directly to the customer.
		self::dispatch( 'admin_notification', $to, $subject, $body, $customer_email );
	}

	/**
	 * Send auto-reply email to the customer.
	 *
	 * @param array $lead_data Associative array of lead information.
	 */
	public static function send_auto_reply( $lead_data ) {
		$to = isset( $lead_data['customer_email'] ) ? $lead_data['customer_email'] : '';

		if ( empty( $to ) || ! is_email( $to ) ) {
			self::log( 'AUTO-REPLY SKIPPED: invalid or empty customer email [' . $to . ']' );
			return;
		}

		$subject = get_option( 'virtu_auto_reply_subject', 'We received your request!' );

		$message_template = get_option(
			'virtu_auto_reply_message',
			'Hi {customer_name}, thank you for your interest in {product_name}. We will contact you shortly.'
		);

		$message_content = str_replace(
			array( '{customer_name}', '{product_name}', '{product_url}', '{preferred_date}', '{preferred_time}' ),
			array(
				isset( $lead_data['customer_name'] ) ? $lead_data['customer_name'] : '',
				isset( $lead_data['product_name'] ) ? $lead_data['product_name'] : '',
				isset( $lead_data['product_url'] ) ? $lead_data['product_url'] : '',
				isset( $lead_data['preferred_date'] ) ? $lead_data['preferred_date'] : '',
				isset( $lead_data['preferred_time'] ) ? $lead_data['preferred_time'] : '',
			),
			$message_template
		);

		// Build HTML body.
		$body  = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>';
		$body .= '<body style="font-family:Arial,Helvetica,sans-serif;background:#f4f4f7;padding:20px;">';
		$body .= '<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">';
		$body .= '<div style="background:#3c1a0e;padding:24px 32px;">';
		$body .= '<h1 style="color:#ffffff;margin:0;font-size:20px;">Thank You!</h1>';
		$body .= '</div>';
		$body .= '<div style="padding:32px;">';
		$body .= '<div style="font-size:15px;line-height:1.6;color:#333;">' . nl2br( esc_html( $message_content ) ) . '</div>';
		$body .= '</div>';
		$body .= '<div style="padding:16px 32px;background:#fafafa;border-top:1px solid #eee;text-align:center;"><p style="margin:0;font-size:12px;color:#999;">Sent by VirtuConnect for WooCommerce</p></div>';
		$body .= '</div></body></html>';

		self::dispatch( 'customer_auto_reply', $to, $subject, $body, '' );
	}

	/**
	 * Core dispatcher: tries Resend API first (if enabled), then falls back to wp_mail().
	 *
	 * @param string $email_type  A label for logging ('admin_notification' or 'customer_auto_reply').
	 * @param string $to          Recipient email address.
	 * @param string $subject     Email subject line.
	 * @param string $body        HTML email body.
	 * @param string $reply_to    Optional Reply-To email address.
	 */
	private static function dispatch( $email_type, $to, $subject, $body, $reply_to = '' ) {
		$use_resend = get_option( 'virtu_use_resend', 'no' );
		$api_key    = get_option( 'virtu_resend_api_key', '' );
		$from_email = get_option( 'virtu_resend_from_email', '' );

		$resend_success = false;

		// --- Attempt Resend API ---
		if ( 'yes' === $use_resend && ! empty( $api_key ) ) {

			// Validate that from_email is NOT a free provider — Resend requires a verified domain.
			$blocked_providers = array( 'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'live.com' );
			$from_domain       = strtolower( substr( strrchr( $from_email, '@' ), 1 ) );

			if ( empty( $from_email ) ) {
				self::log( '[' . $email_type . '] Resend SKIPPED: "From Email" not set in plugin settings.' );
			} elseif ( in_array( $from_domain, $blocked_providers, true ) ) {
				self::log( '[' . $email_type . '] Resend SKIPPED: "From Email" (' . $from_email . ') is a free email provider. Resend requires a verified domain email (e.g. noreply@yourdomain.com).' );
			} else {
				self::log( '[' . $email_type . '] Attempting Resend: TO=[' . $to . '] FROM=[' . $from_email . ']' );

				$resend_payload = array(
				'from'    => get_bloginfo( 'name' ) . ' <' . $from_email . '>',
				'to'      => array( $to ),
				'subject' => $subject,
				'html'    => $body,
			);

			if ( ! empty( $reply_to ) && is_email( $reply_to ) ) {
				$resend_payload['reply_to'] = $reply_to;
			}

			$response = wp_remote_post(
				'https://api.resend.com/emails',
				array(
					'method'  => 'POST',
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode( $resend_payload ),
					'timeout' => 15,
				)
			);

				if ( is_wp_error( $response ) ) {
					self::log( '[' . $email_type . '] Resend WP_Error: ' . $response->get_error_message() );
				} else {
					$status_code = wp_remote_retrieve_response_code( $response );
					$resp_body   = wp_remote_retrieve_body( $response );

					if ( $status_code >= 200 && $status_code < 300 ) {
						$resend_success = true;
						self::log( '[' . $email_type . '] Resend SUCCESS (HTTP ' . $status_code . '): TO=[' . $to . ']' );
					} else {
						self::log( '[' . $email_type . '] Resend FAILED (HTTP ' . $status_code . '): ' . $resp_body );
					}
				}
			}
		}

		// --- Fallback to wp_mail() ---
		if ( ! $resend_success ) {
			self::log( '[' . $email_type . '] Falling back to wp_mail(): TO=[' . $to . ']' );
			self::send_via_wp_mail( $email_type, $to, $subject, $body, $reply_to );
		}
	}

	/**
	 * Send email using wp_mail() with proper headers for reliable delivery.
	 *
	 * @param string $email_type A label used for logging.
	 * @param string $to         Recipient email address.
	 * @param string $subject    Email subject line.
	 * @param string $body       HTML email body.
	 * @param string $reply_to   Optional Reply-To email address.
	 */
	private static function send_via_wp_mail( $email_type, $to, $subject, $body, $reply_to = '' ) {
		$site_name   = get_bloginfo( 'name' );
		$from_name   = ! empty( $site_name ) ? $site_name : 'VirtuConnect';

		// Try to use admin_email domain (proper domain = better deliverability).
		// Fall back to site domain if admin_email looks weird.
		$admin_email      = get_option( 'admin_email', '' );
		$admin_email_part = strtolower( substr( strrchr( $admin_email, '@' ), 1 ) );
		$site_domain      = wp_parse_url( get_home_url(), PHP_URL_HOST );

		$blocked = array( 'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'live.com' );
		if ( ! empty( $admin_email_part ) && ! in_array( $admin_email_part, $blocked, true ) ) {
			$from_email = 'noreply@' . $admin_email_part;
		} else {
			$from_email = 'noreply@' . $site_domain;
		}

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'MIME-Version: 1.0',
			'From: ' . $from_name . ' <' . $from_email . '>',
		);

		if ( ! empty( $reply_to ) && is_email( $reply_to ) ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}

		// Force PHPMailer to HTML mode at the engine level.
		add_action( 'phpmailer_init', array( 'Virtu_Email', 'force_html_mailer' ) );

		$sent = wp_mail( $to, $subject, $body, $headers );

		remove_action( 'phpmailer_init', array( 'Virtu_Email', 'force_html_mailer' ) );

		if ( $sent ) {
			self::log( '[' . $email_type . '] wp_mail() SUCCESS: TO=[' . $to . ']' );
		} else {
			global $phpmailer;
			$mailer_error = '';
			if ( isset( $phpmailer ) && is_object( $phpmailer ) && ! empty( $phpmailer->ErrorInfo ) ) {
				$mailer_error = $phpmailer->ErrorInfo;
			}
			self::log(
				'[' . $email_type . '] wp_mail() FAILED: TO=[' . $to . ']'
				. ( $mailer_error ? ' | PHPMailer Error: ' . $mailer_error : ' | No PHPMailer error info available.' )
			);
		}
	}

	/**
	 * Force PHPMailer to use HTML content type.
	 * Hooked onto phpmailer_init.
	 *
	 * @param PHPMailer $phpmailer The PHPMailer instance.
	 */
	public static function force_html_mailer( $phpmailer ) {
		$phpmailer->isHTML( true );
		$phpmailer->ContentType = 'text/html';
	}

	/**
	 * AJAX handler: send a test email to the admin address.
	 * Used by the "Send Test Email" button in the plugin settings.
	 */
	public static function handle_test_email() {
		if ( ! check_ajax_referer( 'virtu_test_email_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Security check failed.' );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$type = sanitize_text_field( isset( $_POST['email_type'] ) ? $_POST['email_type'] : 'admin' );

		$test_lead = array(
			'product_id'     => 0,
			'product_name'   => 'Test Product',
			'product_url'    => home_url(),
			'product_price'  => '9,999',
			'customer_name'  => 'Test Customer',
			'customer_email' => get_option( 'admin_email' ),
			'customer_phone' => '+91 98765 43210',
			'preferred_date' => gmdate( 'Y-m-d' ),
			'preferred_time' => '10:00 AM',
			'message'        => 'This is a test submission from VirtuConnect.',
		);

		if ( 'customer' === $type ) {
			// Send test auto-reply to admin's own inbox so they can see it.
			Virtu_Email::send_auto_reply( $test_lead );
			wp_send_json_success( 'Test customer auto-reply sent to ' . get_option( 'admin_email' ) . '. Check virtu-error.log for details.' );
		} else {
			Virtu_Email::send_notification( $test_lead );
			wp_send_json_success( 'Test admin notification sent to ' . get_option( 'virtu_admin_email', get_option( 'admin_email' ) ) . '. Check virtu-error.log for details.' );
		}
	}

	/**
	 * Write a timestamped entry to the plugin error log.
	 *
	 * @param string $message Message to log.
	 */
	public static function log( $message ) {
		$entry = '[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message . PHP_EOL;
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@file_put_contents( VIRTU_PATH . 'virtu-error.log', $entry, FILE_APPEND | LOCK_EX );
	}
}

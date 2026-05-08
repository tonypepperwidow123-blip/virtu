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

		// Fallback to site admin email if the option is blank.
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

		// Header.
		$body .= '<div style="background:#3c1a0e;padding:24px 32px;">';
		$body .= '<h1 style="color:#ffffff;margin:0;font-size:20px;">&#x1F4F9; New Video Call Request</h1>';
		$body .= '</div>';

		// Content.
		$body .= '<div style="padding:32px;">';
		$body .= '<h2 style="color:#333;font-size:16px;margin:0 0 16px;border-bottom:2px solid #f0f0f0;padding-bottom:8px;">Customer Details</h2>';
		$body .= '<table style="width:100%;border-collapse:collapse;margin-bottom:24px;">';

		$body .= '<tr><td style="padding:8px 12px;color:#666;font-size:14px;width:140px;">Name</td>'
			. '<td style="padding:8px 12px;color:#333;font-size:14px;font-weight:600;">' . esc_html( $customer_name ) . '</td></tr>';

		$body .= '<tr style="background:#fafafa;"><td style="padding:8px 12px;color:#666;font-size:14px;">Email</td>'
			. '<td style="padding:8px 12px;color:#333;font-size:14px;"><a href="mailto:' . esc_attr( $customer_email ) . '" style="color:#3c1a0e;">'
			. esc_html( $customer_email ) . '</a></td></tr>';

		$body .= '<tr><td style="padding:8px 12px;color:#666;font-size:14px;">Phone</td>'
			. '<td style="padding:8px 12px;color:#333;font-size:14px;"><a href="tel:' . esc_attr( $customer_phone ) . '" style="color:#3c1a0e;">'
			. esc_html( $customer_phone ) . '</a></td></tr>';

		if ( ! empty( $lead_data['preferred_date'] ) ) {
			$body .= '<tr style="background:#fafafa;"><td style="padding:8px 12px;color:#666;font-size:14px;">Preferred Date</td>'
				. '<td style="padding:8px 12px;color:#333;font-size:14px;">' . esc_html( $lead_data['preferred_date'] ) . '</td></tr>';
		}

		if ( ! empty( $lead_data['preferred_time'] ) ) {
			$body .= '<tr><td style="padding:8px 12px;color:#666;font-size:14px;">Preferred Time</td>'
				. '<td style="padding:8px 12px;color:#333;font-size:14px;">' . esc_html( $lead_data['preferred_time'] ) . '</td></tr>';
		}

		if ( ! empty( $lead_data['message'] ) ) {
			$body .= '<tr style="background:#fafafa;"><td style="padding:8px 12px;color:#666;font-size:14px;">Message</td>'
				. '<td style="padding:8px 12px;color:#333;font-size:14px;">' . esc_html( $lead_data['message'] ) . '</td></tr>';
		}

		$body .= '</table>';

		// Product details.
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
		$body .= '</div>'; // End content.

		// Footer.
		$body .= '<div style="padding:16px 32px;background:#fafafa;border-top:1px solid #eee;text-align:center;">';
		$body .= '<p style="margin:0;font-size:12px;color:#999;">Sent by VirtuConnect for WooCommerce</p>';
		$body .= '</div>';

		$body .= '</div>'; // End wrapper.
		$body .= '</body></html>';

		self::dispatch( $to, $subject, $body, $customer_email );
	}

	/**
	 * Send auto-reply email to the customer.
	 *
	 * @param array $lead_data Associative array of lead information.
	 */
	public static function send_auto_reply( $lead_data ) {
		$to = isset( $lead_data['customer_email'] ) ? $lead_data['customer_email'] : '';

		if ( empty( $to ) || ! is_email( $to ) ) {
			self::log( 'Auto-reply skipped: invalid or empty customer email.' );
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

		// Header.
		$body .= '<div style="background:#3c1a0e;padding:24px 32px;">';
		$body .= '<h1 style="color:#ffffff;margin:0;font-size:20px;">Thank You!</h1>';
		$body .= '</div>';

		// Body.
		$body .= '<div style="padding:32px;">';
		$body .= '<div style="font-size:15px;line-height:1.6;color:#333;">' . nl2br( esc_html( $message_content ) ) . '</div>';
		$body .= '</div>';

		// Footer.
		$body .= '<div style="padding:16px 32px;background:#fafafa;border-top:1px solid #eee;text-align:center;">';
		$body .= '<p style="margin:0;font-size:12px;color:#999;">Sent by VirtuConnect for WooCommerce</p>';
		$body .= '</div>';

		$body .= '</div>';
		$body .= '</body></html>';

		self::dispatch( $to, $subject, $body, '' );
	}

	/**
	 * Core dispatcher: tries Resend API first (if enabled), then falls back to wp_mail().
	 *
	 * @param string $to         Recipient email address.
	 * @param string $subject    Email subject line.
	 * @param string $body       HTML email body.
	 * @param string $reply_to   Optional Reply-To email (e.g. the customer's email for admin mails).
	 */
	private static function dispatch( $to, $subject, $body, $reply_to = '' ) {
		$use_resend = get_option( 'virtu_use_resend', 'no' );
		$api_key    = get_option( 'virtu_resend_api_key', '' );

		$resend_success = false;

		if ( 'yes' === $use_resend && ! empty( $api_key ) ) {
			$from_email = get_option( 'virtu_resend_from_email', get_option( 'admin_email' ) );

			$response = wp_remote_post(
				'https://api.resend.com/emails',
				array(
					'method'  => 'POST',
					'headers' => array(
						'Authorization' => 'Bearer ' . $api_key,
						'Content-Type'  => 'application/json',
					),
					'body'    => wp_json_encode(
						array(
							'from'    => 'VirtuConnect <' . $from_email . '>',
							'to'      => array( $to ),
							'subject' => $subject,
							'html'    => $body,
						)
					),
					'timeout' => 15,
				)
			);

			if ( is_wp_error( $response ) ) {
				self::log( 'Resend API Error: ' . $response->get_error_message() );
			} else {
				$status_code = wp_remote_retrieve_response_code( $response );
				if ( $status_code >= 200 && $status_code < 300 ) {
					$resend_success = true;
				} else {
					$error_body = wp_remote_retrieve_body( $response );
					self::log( 'Resend API Rejected (HTTP ' . $status_code . '): ' . $error_body );
				}
			}
		}

		// Use wp_mail() if Resend is disabled or failed.
		if ( ! $resend_success ) {
			self::send_via_wp_mail( $to, $subject, $body, $reply_to );
		}
	}

	/**
	 * Send email using WordPress wp_mail() with proper headers for reliable delivery.
	 *
	 * @param string $to       Recipient email address.
	 * @param string $subject  Email subject line.
	 * @param string $body     HTML email body.
	 * @param string $reply_to Optional Reply-To email address.
	 */
	private static function send_via_wp_mail( $to, $subject, $body, $reply_to = '' ) {
		// Derive a clean From name and address using the site domain.
		$site_name   = get_bloginfo( 'name' );
		$site_domain = wp_parse_url( get_home_url(), PHP_URL_HOST );
		$from_name   = ! empty( $site_name ) ? $site_name : 'VirtuConnect';
		$from_email  = 'noreply@' . $site_domain;

		// Build reliable headers.
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . $from_name . ' <' . $from_email . '>',
			'MIME-Version: 1.0',
		);

		// Add Reply-To if provided (useful for admin notification so admin can
		// reply directly to the customer).
		if ( ! empty( $reply_to ) && is_email( $reply_to ) ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}

		// Hook phpmailer to force HTML content type at the PHPMailer level
		// (some hosting environments override wp_mail headers).
		add_action( 'phpmailer_init', array( 'Virtu_Email', 'force_html_mailer' ) );

		$sent = wp_mail( $to, $subject, $body, $headers );

		// Unhook immediately so we don't affect other wp_mail calls on the page.
		remove_action( 'phpmailer_init', array( 'Virtu_Email', 'force_html_mailer' ) );

		if ( ! $sent ) {
			// Capture the last PHP mailer error for debugging.
			global $phpmailer;
			$mailer_error = '';
			if ( isset( $phpmailer ) && is_object( $phpmailer ) && ! empty( $phpmailer->ErrorInfo ) ) {
				$mailer_error = $phpmailer->ErrorInfo;
			}
			self::log(
				'wp_mail() failed sending to [' . $to . '] | Subject: [' . $subject . ']'
				. ( $mailer_error ? ' | PHPMailer: ' . $mailer_error : '' )
			);
		}
	}

	/**
	 * Force PHPMailer to use HTML content type.
	 * Hooked onto phpmailer_init to override any host-level defaults.
	 *
	 * @param PHPMailer $phpmailer The PHPMailer instance.
	 */
	public static function force_html_mailer( $phpmailer ) {
		$phpmailer->isHTML( true );
		$phpmailer->ContentType = 'text/html';
	}

	/**
	 * Write a timestamped entry to the plugin error log.
	 *
	 * @param string $message Message to log.
	 */
	private static function log( $message ) {
		$entry = '[' . gmdate( 'Y-m-d H:i:s' ) . '] ' . $message . PHP_EOL;
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@file_put_contents( VIRTU_PATH . 'virtu-error.log', $entry, FILE_APPEND | LOCK_EX );
	}
}

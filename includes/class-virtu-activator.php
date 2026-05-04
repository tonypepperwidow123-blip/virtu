<?php
/**
 * Fired during plugin activation.
 *
 * Creates the custom database table and sets default options.
 *
 * @package VirtuConnect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Virtu_Activator
 *
 * Handles all tasks that need to run on plugin activation.
 */
class Virtu_Activator {

	/**
	 * Run the activation routines.
	 *
	 * Creates the leads database table and populates default option values.
	 */
	public static function activate() {
		self::create_table();
		self::set_default_options();
	}

	/**
	 * Create the virtu_leads table using dbDelta().
	 */
	private static function create_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . 'virtu_leads';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			product_id bigint(20) unsigned NOT NULL DEFAULT 0,
			product_name varchar(255) NOT NULL DEFAULT '',
			product_url text NOT NULL,
			product_price varchar(50) NOT NULL DEFAULT '',
			customer_name varchar(255) NOT NULL DEFAULT '',
			customer_email varchar(255) NOT NULL DEFAULT '',
			customer_phone varchar(50) NOT NULL DEFAULT '',
			preferred_date date DEFAULT NULL,
			preferred_time varchar(20) NOT NULL DEFAULT '',
			message text,
			status varchar(20) NOT NULL DEFAULT 'new',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY product_id (product_id),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'virtu_db_version', VIRTU_VERSION );
	}

	/**
	 * Set all default plugin options if they don't already exist.
	 */
	private static function set_default_options() {
		$defaults = array(
			// General.
			'virtu_enabled'           => 'yes',
			'virtu_display_hook'      => 'woocommerce_single_product_summary',
			'virtu_display_priority'  => '35',
			'virtu_section_title'     => 'Experience the virtual shopping experience',

			// Video Call.
			'virtu_vc_enabled'        => 'yes',
			'virtu_vc_title'          => 'Video Call Us',
			'virtu_vc_description'    => 'Get on a video chat with us to take a closer look',
			'virtu_vc_button_text'    => 'Video call',
			'virtu_form_show_date'    => 'yes',
			'virtu_form_show_time'    => 'yes',
			'virtu_form_show_message' => 'yes',
			'virtu_popup_title'       => 'Schedule a Video Call',
			'virtu_popup_subtitle'    => 'Please provide your details to continue',

			// Email.
			'virtu_admin_email'         => get_option( 'admin_email' ),
			'virtu_email_subject'       => 'New Video Call Request - {product_name}',
			'virtu_auto_reply'          => 'yes',
			'virtu_auto_reply_subject'  => 'We received your request!',
			'virtu_auto_reply_message'  => 'Hi {customer_name}, thank you for your interest in {product_name}. We will contact you shortly. View product: {product_url}',

			// WhatsApp.
			'virtu_wa_enabled'          => 'yes',
			'virtu_wa_number'           => '',
			'virtu_wa_title'            => 'WhatsApp',
			'virtu_wa_description'      => 'Get WhatsApp Assistance - Chat with us',
			'virtu_wa_button_text'      => 'Chat with Us',
			'virtu_wa_message_template' => 'Hi, I\'m interested in {product_name} (₹{product_price}). Can you share more details? {product_url}',

			// Design.
			'virtu_vc_box_bg'           => '#fdf5e6',
			'virtu_wa_box_bg'           => '#f0faf0',
			'virtu_vc_btn_bg'           => '#3c1a0e',
			'virtu_vc_btn_text_color'   => '#ffffff',
			'virtu_wa_btn_bg'           => '#1a7a3c',
			'virtu_wa_btn_text_color'   => '#ffffff',
			'virtu_box_border_radius'   => '12',
			'virtu_btn_border_radius'   => '8',
			'virtu_section_font_size'   => '14',
		);

		foreach ( $defaults as $key => $value ) {
			if ( false === get_option( $key ) ) {
				update_option( $key, $value );
			}
		}
	}
}
